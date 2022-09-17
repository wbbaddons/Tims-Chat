<?php

/*
 * Copyright (c) 2010-2022 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2026-09-17
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

namespace chat\page;

use chat\data\message\Message;
use chat\data\message\MessageList;
use chat\data\room\RoomCache;
use wcf\data\object\type\ObjectTypeCache;
use wcf\page\AbstractPage;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\page\PageLocationManager;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;

/**
 * Shows the log of a specific chat room.
 */
final class LogPage extends AbstractPage
{
    use TConfiguredPage;

    /**
     * @inheritDoc
     */
    public $loginRequired = true;

    /**
     * The requested chat room ID.
     * @var int
     */
    public $roomID = 0;

    /**
     * The requested chat room.
     * @var \chat\data\room\Room
     */
    public $room;

    /**
     * The requested message ID.
     * @var int
     */
    public $messageID = 0;

    /**
     * The requested message.
     * @var \chat\data\message\Message
     */
    public $message;

    /**
     * The requested time.
     * @var int
     */
    public $datetime = 0;

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (isset($_GET['id'])) {
            $this->roomID = \intval($_GET['id']);
        }
        $this->room = RoomCache::getInstance()->getRoom($this->roomID);

        if ($this->room === null) {
            throw new IllegalLinkException();
        }
        if (!$this->room->canSee($user = null, $reason)) {
            throw $reason;
        }
        if (!$this->room->canSeeLog($user = null, $reason)) {
            throw $reason;
        }

        if (isset($_GET['messageid'])) {
            $this->messageID = \intval($_GET['messageid']);
        }
        if ($this->messageID) {
            $this->message = new Message($this->messageID);
            if (!$this->message->getMessageType()->getProcessor()->canSeeInLog($this->message, $this->room)) {
                throw new PermissionDeniedException();
            }
        }

        if (isset($_REQUEST['datetime'])) {
            $this->datetime = \strtotime($_REQUEST['datetime']);
        }
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        if ($this->datetime) {
            // Determine message types supporting fast select
            $objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('be.bastelstu.chat.messageType');
            $fastSelect = \array_map(static function ($item) {
                return $item->objectTypeID;
            }, \array_filter($objectTypes, static function ($item) {
                // TODO: Consider a method couldAppearInLog(): bool
                return $item->getProcessor()->supportsFastSelect();
            }));

            $minimum = 0;
            $loops = 0;
            do {
                // Build fast select filter
                $condition = new PreparedStatementConditionBuilder();
                $condition->add('((roomID = ? AND objectTypeID IN (?)) OR objectTypeID NOT IN (?))', [ $this->room->roomID, $fastSelect, $fastSelect ]);
                $condition->add('time >= ?', [ $this->datetime ]);
                if ($minimum) {
                    $condition->add('messageID > ?', [ $minimum ]);
                }

                $sql = "SELECT  messageID
                        FROM    chat1_message
                        {$condition}
                        ORDER BY messageID ASC";
                $statement = WCF::getDB()->prepare($sql, 20);
                $statement->execute($condition->getParameters());
                $messageIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);

                $objectList = new MessageList();
                $objectList->setObjectIDs($messageIDs);
                $objectList->readObjects();
                $objects = $objectList->getObjects();
                if (empty($objects)) {
                    // TODO: UserInputException?
                    throw new IllegalLinkException();
                }

                foreach ($objects as $message) {
                    if ($message->getMessageType()->getProcessor()->canSeeInLog($message, $this->room)) {
                        $parameters = [
                            'messageid' => $message->messageID,
                            'object' => $this->room,
                        ];
                        HeaderUtil::redirect(LinkHandler::getInstance()->getControllerLink(self::class, $parameters));

                        exit;
                    }
                    $minimum = $message->messageID;
                }
            } while (++$loops <= 3);

            // Do a best guess redirect to an ID that is as near as possible
            $parameters = [
                'application' => 'chat',
                'messageid' => $minimum,
                'object' => $this->room,
            ];
            HeaderUtil::redirect(LinkHandler::getInstance()->getControllerLink(self::class, $parameters));

            exit;
        }
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        PageLocationManager::getInstance()->addParentLocation(
            'be.bastelstu.chat.Room',
            $this->room->roomID,
            $this->room
        );
        WCF::getTPL()->assign([
            'room' => $this->room,
            'roomList' => RoomCache::getInstance()->getRooms(),
            'messageID' => $this->messageID,
            'message' => $this->message,
            'config' => $this->getConfig(),
        ]);
    }
}
