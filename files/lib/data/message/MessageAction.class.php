<?php

/*
 * Copyright (c) 2010-2022 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2026-03-04
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

namespace chat\data\message;

use chat\data\command\CommandCache;
use chat\data\room\RoomCache;
use chat\data\user\User as ChatUser;
use chat\data\user\UserAction as ChatUserAction;
use chat\system\message\type\IDeletableMessageType;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\attachment\AttachmentHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\push\PushHandler;
use wcf\system\user\activity\point\UserActivityPointHandler;
use wcf\system\WCF;

/**
 * Executes chat user-related actions.
 */
class MessageAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
    public function create()
    {
        $message = parent::create();

        if (isset($this->parameters['updateTimestamp']) && $this->parameters['updateTimestamp']) {
            $sql = "UPDATE  chat1_room_to_user
                    SET     lastPush = ?
                    WHERE   roomID = ?
                        AND userID = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([
                TIME_NOW,
                $message->roomID,
                $message->userID,
            ]);
        }
        if (isset($this->parameters['grantPoints']) && $this->parameters['grantPoints']) {
            UserActivityPointHandler::getInstance()->fireEvent(
                'be.bastelstu.chat.activityPointEvent.message',
                $message->messageID,
                $message->userID
            );
        }

        $pushHandler = PushHandler::getInstance();
        if ($pushHandler->isEnabled() && \in_array('target:channels', $pushHandler->getFeatureFlags())) {
            $fastSelect = $message->getMessageType()->getProcessor()->supportsFastSelect();
            if ($fastSelect) {
                $target = [
                    'channels' => [
                        'be.bastelstu.chat.room-' . $message->roomID,
                    ],
                ];
            } else {
                $target = [
                    'channels' => [
                        'be.bastelstu.chat',
                    ],
                ];
            }
            $pushHandler->sendMessage([
                'message' => 'be.bastelstu.chat.message',
                'target' => $target,
            ]);
        }

        return $message;
    }

    /**
     * Validates parameters and permissions.
     */
    public function validateTrash()
    {
        // read objects
        if (empty($this->objects)) {
            $this->readObjects();

            if (empty($this->objects)) {
                throw new UserInputException('objectIDs');
            }
        }

        foreach ($this->getObjects() as $message) {
            if ($message->isDeleted) {
                continue;
            }

            $messageType = $message->getMessageType()->getProcessor();
            if (
                !($messageType instanceof IDeletableMessageType)
                || !$messageType->canDelete($message->getDecoratedObject())
            ) {
                throw new PermissionDeniedException();
            }
        }
    }

    /**
     * Marks this message as deleted and creates a tombstone message.
     *
     * Note: Contrary to other applications there is no way to undelete a message.
     */
    public function trash()
    {
        if (empty($this->objects)) {
            $this->readObjects();
        }

        $data = [
            'isDeleted' => 1,
        ];

        $objectTypeID = ObjectTypeCache::getInstance()->getObjectTypeIDByName(
            'be.bastelstu.chat.messageType',
            'be.bastelstu.chat.messageType.tombstone'
        );
        if (!$objectTypeID) {
            throw new \LogicException('Missing object type');
        }

        WCF::getDB()->beginTransaction();
        $objectAction = new static(
            $this->getObjects(),
            'update',
            [
                'data' => $data,
            ]
        );
        $objectAction->executeAction();
        foreach ($this->getObjects() as $message) {
            if ($message->isDeleted) {
                continue;
            }

            (new self(
                [ ],
                'create',
                [
                    'data' => [
                        'roomID' => $message->roomID,
                        'userID' => null,
                        'username' => '',
                        'time' => TIME_NOW,
                        'objectTypeID' => $objectTypeID,
                        'payload' => \serialize([
                            'messageID' => $message->messageID,
                        ]),
                    ],
                ]
            ))->executeAction();
        }
        WCF::getDB()->commitTransaction();
    }

    /**
     * Prunes chat messages older than chat_log_archivetime days.
     */
    public function prune()
    {
        // Check whether pruning is disabled.
        if (!CHAT_LOG_ARCHIVETIME) {
            return;
        }

        $sql = "SELECT  messageID
                FROM    chat1_message
                WHERE   time < ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([ TIME_NOW - CHAT_LOG_ARCHIVETIME * 86400 ]);
        $messageIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);

        return \call_user_func(
            [$this->className, 'deleteAll'],
            $messageIDs
        );
    }

    /**
     * Validates parameters and permissions.
     */
    public function validatePull()
    {
        $this->readString('sessionID', true);
        if ($this->parameters['sessionID']) {
            $this->parameters['sessionID'] = \pack(
                'H*',
                \str_replace('-', '', $this->parameters['sessionID'])
            );
        }

        $this->readInteger('roomID');
        $this->readBoolean('inLog', true);

        $room = RoomCache::getInstance()->getRoom($this->parameters['roomID']);
        if ($room === null) {
            throw new UserInputException('roomID');
        }
        if (!$room->canSee($user = null, $reason)) {
            throw $reason;
        }
        $user = new ChatUser(WCF::getUser());
        if (!$this->parameters['inLog'] && !$user->isInRoom($room)) {
            throw new PermissionDeniedException();
        }
        if ($this->parameters['inLog'] && !$room->canSeeLog(null, $reason)) {
            throw $reason;
        }

        $this->readInteger('from', true);
        $this->readInteger('to', true);

        // One may not pass both 'from' and 'to'
        if ($this->parameters['from'] && $this->parameters['to']) {
            throw new UserInputException();
        }
    }

    /**
     * Pulls messages for the given room.
     */
    public function pull()
    {
        $room = RoomCache::getInstance()->getRoom($this->parameters['roomID']);
        if ($room === null) {
            throw new UserInputException('roomID');
        }

        if (($sessionID = $this->parameters['sessionID'])) {
            if (\strlen($sessionID) !== 16) {
                throw new UserInputException('sessionID');
            }

            (new ChatUserAction([], 'clearDeadSessions'))->executeAction();

            WCF::getDB()->beginTransaction();
            // update timestamp
            $sql = "UPDATE  chat1_room_to_user
                    SET     lastPull = ?
                    WHERE   roomID = ?
                        AND userID = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([ TIME_NOW,
                $room->roomID,
                WCF::getUser()->userID,
            ]);

            $sql = "UPDATE  chat1_session
                    SET     lastRequest = ?
                    WHERE   roomID = ?
                        AND userID = ?
                        AND sessionID = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([ TIME_NOW,
                $room->roomID,
                WCF::getUser()->userID,
                $sessionID,
            ]);
            WCF::getDB()->commitTransaction();
        }

        // Determine message types supporting fast select
        $objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('be.bastelstu.chat.messageType');
        $fastSelect = \array_map(static function ($item) {
            return $item->objectTypeID;
        }, \array_filter($objectTypes, static function ($item) {
            return $item->getProcessor()->supportsFastSelect();
        }));

        // Build fast select filter
        $condition = new PreparedStatementConditionBuilder();
        $condition->add('((roomID = ? AND objectTypeID IN (?)) OR objectTypeID NOT IN (?))', [ $room->roomID, $fastSelect, $fastSelect ]);

        $sortOrder = 'DESC';
        // Add offset
        if ($this->parameters['from']) {
            $condition->add('messageID >= ?', [ $this->parameters['from'] ]);
            $sortOrder = 'ASC';
        }
        if ($this->parameters['to']) {
            $condition->add('messageID <= ?', [ $this->parameters['to'] ]);
        }

        $sql = "SELECT   messageID
                FROM     chat1_message
                " . $condition . "
                ORDER BY messageID " . $sortOrder;
        $statement = WCF::getDB()->prepare($sql, 20);
        $statement->execute($condition->getParameters());
        $messageIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);

        $objectList = new MessageList();
        $objectList->setObjectIDs($messageIDs);
        $objectList->readObjects();
        $objects = $objectList->getObjects();

        $canSeeLog = $room->canSeeLog();
        $messages = \array_map(static function (Message $item) use ($room) {
            return new ViewableMessage($item, $room);
        }, \array_filter($objects, function (Message $message) use ($canSeeLog, $room) {
            if ($this->parameters['inLog'] || $message->isInLog()) {
                return $canSeeLog && $message->getMessageType()->getProcessor()->canSeeInLog($message, $room);
            } else {
                return $message->getMessageType()->getProcessor()->canSee($message, $room);
            }
        }));

        $embeddedObjectMessageIDs = \array_map(static function ($message) {
            return $message->messageID;
        }, \array_filter($messages, static function ($message) {
            return $message->hasEmbeddedObjects;
        }));

        if ($embeddedObjectMessageIDs !== []) {
            // load embedded objects
            MessageEmbeddedObjectManager::getInstance()->loadObjects('be.bastelstu.chat.message', $embeddedObjectMessageIDs);
        }

        return [
            'messages' => $messages,
            'from' => $this->parameters['from'] ?: (!empty($objects) ? \reset($objects)->messageID : $this->parameters['to'] + 1),
            'to' => $this->parameters['to'] ?: (!empty($objects) ? \end($objects)->messageID : $this->parameters['from'] - 1),
        ];
    }

    /**
     * Validates parameters and permissions.
     */
    public function validatePush()
    {
        $this->readInteger('roomID');

        $room = RoomCache::getInstance()->getRoom($this->parameters['roomID']);
        if ($room === null) {
            throw new UserInputException('roomID');
        }
        if (!$room->canSee($user = null, $reason)) {
            throw $reason;
        }
        $user = new ChatUser(WCF::getUser());
        if (!$user->isInRoom($room)) {
            throw new PermissionDeniedException();
        }

        $this->readInteger('commandID');
        $command = CommandCache::getInstance()->getCommand($this->parameters['commandID']);
        if ($command === null) {
            throw new UserInputException('commandID');
        }
        if (!$command->hasTriggers()) {
            if (!$command->getProcessor()->allowWithoutTrigger()) {
                throw new UserInputException('commandID');
            }
        }

        $this->readJSON('parameters', true);
    }

    /**
     * Pushes a new message into the given room.
     */
    public function push()
    {
        $room = RoomCache::getInstance()->getRoom($this->parameters['roomID']);
        if ($room === null) {
            throw new UserInputException('roomID');
        }

        $command = CommandCache::getInstance()->getCommand($this->parameters['commandID']);
        if ($command === null) {
            throw new UserInputException('commandID');
        }

        $processor = $command->getProcessor();
        $processor->validate($this->parameters['parameters'], $room);
        $processor->execute($this->parameters['parameters'], $room);
    }

    /**
     * Validates parameters and permissions.
     */
    public function validatePushAttachment()
    {
        $this->readInteger('roomID');

        $room = RoomCache::getInstance()->getRoom($this->parameters['roomID']);
        if ($room === null) {
            throw new UserInputException('roomID');
        }
        if (!$room->canSee($user = null, $reason)) {
            throw $reason;
        }
        $user = new ChatUser(WCF::getUser());
        if (!$user->isInRoom($room)) {
            throw new PermissionDeniedException();
        }

        if (!$room->canWritePublicly(null, $reason)) {
            throw $reason;
        }

        $this->readString('tmpHash');
    }

    /**
     * Pushes a new attachment into the given room.
     */
    public function pushAttachment()
    {
        $objectTypeID = ObjectTypeCache::getInstance()->getObjectTypeIDByName(
            'be.bastelstu.chat.messageType',
            'be.bastelstu.chat.messageType.attachment'
        );
        if (!$objectTypeID) {
            throw new \LogicException('Missing object type');
        }

        $room = RoomCache::getInstance()->getRoom($this->parameters['roomID']);
        if ($room === null) {
            throw new UserInputException('roomID');
        }

        $attachmentHandler = new AttachmentHandler(
            'be.bastelstu.chat.message',
            0,
            $this->parameters['tmpHash'],
            $room->roomID
        );
        $attachments = $attachmentHandler->getAttachmentList();
        $attachmentIDs = [];
        foreach ($attachments as $attachment) {
            $attachmentIDs[] = $attachment->attachmentID;
        }

        $processor = new HtmlInputProcessor();
        $processor->process(\implode(' ', \array_map(static function ($attachmentID) {
            return '[attach=' . $attachmentID . ',none,true][/attach]';
        }, $attachmentIDs)), 'be.bastelstu.chat.message', 0);

        WCF::getDB()->beginTransaction();
        /** @var Message $message */
        $message = (new self(
            [ ],
            'create',
            [
                'data' => [
                    'roomID' => $room->roomID,
                    'userID' => WCF::getUser()->userID,
                    'username' => WCF::getUser()->username,
                    'time' => TIME_NOW,
                    'objectTypeID' => $objectTypeID,
                    'payload' => \serialize([
                        'attachmentIDs' => $attachmentIDs,
                        'message' => $processor->getHtml(),
                    ]),
                ],
            ]
        ))->executeAction()['returnValues'];

        $attachmentHandler->updateObjectID($message->messageID);
        $processor->setObjectID($message->messageID);
        if (MessageEmbeddedObjectManager::getInstance()->registerObjects($processor)) {
            (new MessageEditor($message))->update([
                'hasEmbeddedObjects' => 1,
            ]);
        }
        WCF::getDB()->commitTransaction();
    }
}
