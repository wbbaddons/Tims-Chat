<?php
/*
 * Copyright (c) 2010-2021 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2025-02-04
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

namespace chat\data\message;

use \chat\data\command\CommandCache;
use \chat\data\room\RoomCache;
use \wcf\data\object\type\ObjectTypeCache;
use \wcf\system\attachment\AttachmentHandler;
use \wcf\system\exception\PermissionDeniedException;
use \wcf\system\exception\UserInputException;
use \wcf\system\user\activity\point\UserActivityPointHandler;
use \wcf\system\WCF;

/**
 * Executes chat user-related actions.
 */
class MessageAction extends \wcf\data\AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	public function create() {
		$message = parent::create();

		if (isset($this->parameters['updateTimestamp']) && $this->parameters['updateTimestamp']) {
			$sql = "UPDATE chat".WCF_N."_room_to_user SET lastPush = ? WHERE roomID = ? AND userID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([ TIME_NOW, $message->roomID, $message->userID ]);
		}
		if (isset($this->parameters['grantPoints']) && $this->parameters['grantPoints']) {
			UserActivityPointHandler::getInstance()->fireEvent('be.bastelstu.chat.activityPointEvent.message', $message->messageID, $message->userID);
		}

		$pushHandler = \wcf\system\push\PushHandler::getInstance();
		if ($pushHandler->isEnabled() && in_array('target:channels', $pushHandler->getFeatureFlags())) {
			$fastSelect = $message->getMessageType()->getProcessor()->supportsFastSelect();
			if ($fastSelect) {
				$target = [ 'channels' => [ 'be.bastelstu.chat.room-'.$message->roomID ] ];
			}
			else {
				$target = [ 'channels' => [ 'be.bastelstu.chat' ] ];
			}
			$pushHandler->sendMessage([ 'message' => 'be.bastelstu.chat.message'
			                          , 'target' => $target
			                          ]);
		}

		return $message;
	}

	/**
	 * Validates parameters and permissions.
	 */
	public function validateTrash() {
		// read objects
		if (empty($this->objects)) {
			$this->readObjects();

			if (empty($this->objects)) {
				throw new UserInputException('objectIDs');
			}
		}

		foreach ($this->getObjects() as $message) {
			if ($message->isDeleted) continue;

			$messageType = $message->getMessageType()->getProcessor();
			if (!($messageType instanceof \chat\system\message\type\IDeletableMessageType) || !$messageType->canDelete($message->getDecoratedObject())) {
				throw new PermissionDeniedException();
			}
		}
	}

	/**
	 * Marks this message as deleted and creates a tombstone message.
	 *
	 * Note: Contrary to other applications there is no way to undelete a message.
	 */
	public function trash() {
		if (empty($this->objects)) {
			$this->readObjects();
		}

		$data = [ 'isDeleted' => 1
		        ];

		$objectTypeID = ObjectTypeCache::getInstance()->getObjectTypeIDByName('be.bastelstu.chat.messageType', 'be.bastelstu.chat.messageType.tombstone');
		if (!$objectTypeID) {
			throw new \LogicException('Missing object type');
		}

		WCF::getDB()->beginTransaction();
		$objectAction = new static($this->getObjects(), 'update', [ 'data' => $data ]);
		$objectAction->executeAction();
		foreach ($this->getObjects() as $message) {
			if ($message->isDeleted) continue;

			(new MessageAction([ ], 'create', [ 'data' => [ 'roomID'       => $message->roomID
			                                              , 'userID'       => null
			                                              , 'username'     => ''
			                                              , 'time'         => TIME_NOW
			                                              , 'objectTypeID' => $objectTypeID
			                                              , 'payload'      => serialize([ 'messageID' => $message->messageID ])
			                                              ]
			                                  ]
			                  )
			)->executeAction();
		}
		WCF::getDB()->commitTransaction();
	}

	/**
	 * Prunes chat messages older than chat_log_archivetime days.
	 */
	public function prune() {
		// Check whether pruning is disabled.
		if (!CHAT_LOG_ARCHIVETIME) return;

		$sql = "SELECT messageID
		        FROM   chat".WCF_N."_message
		        WHERE  time < ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([ TIME_NOW - CHAT_LOG_ARCHIVETIME * 86400 ]);
		$messageIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);

		return call_user_func([$this->className, 'deleteAll'], $messageIDs);
	}

	/**
	 * Validates parameters and permissions.
	 */
	public function validatePull() {
		$this->readString('sessionID', true);
		if ($this->parameters['sessionID']) {
			$this->parameters['sessionID'] = pack('H*', str_replace('-', '', $this->parameters['sessionID']));
		}

		$this->readInteger('roomID');
		$this->readBoolean('inLog', true);

		$room = RoomCache::getInstance()->getRoom($this->parameters['roomID']);
		if ($room === null) throw new UserInputException('roomID');
		if (!$room->canSee($user = null, $reason)) throw $reason;
		$user = new \chat\data\user\User(WCF::getUser());
		if (!$this->parameters['inLog'] && !$user->isInRoom($room)) throw new PermissionDeniedException();
		if ($this->parameters['inLog'] && !$room->canSeeLog(null, $reason)) throw $reason;

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
	public function pull() {
		$room = RoomCache::getInstance()->getRoom($this->parameters['roomID']);
		if ($room === null) throw new UserInputException('roomID');

		if (($sessionID = $this->parameters['sessionID'])) {
			if (strlen($sessionID) !== 16) throw new UserInputException('sessionID');

			(new \chat\data\user\UserAction([], 'clearDeadSessions'))->executeAction();

			WCF::getDB()->beginTransaction();
			// update timestamp
			$sql = "UPDATE chat".WCF_N."_room_to_user
			        SET    lastPull = ?
			        WHERE      roomID = ?
			               AND userID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([ TIME_NOW
			                    , $room->roomID
			                    , WCF::getUser()->userID
			                    ]);

			$sql = "UPDATE chat".WCF_N."_session
			        SET    lastRequest = ?
			        WHERE      roomID = ?
			               AND userID = ?
			               AND sessionID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([ TIME_NOW
			                    , $room->roomID
			                    , WCF::getUser()->userID
			                    , $sessionID
			                    ]);
			WCF::getDB()->commitTransaction();
		}

		// Determine message types supporting fast select
		$objectTypes = \wcf\data\object\type\ObjectTypeCache::getInstance()->getObjectTypes('be.bastelstu.chat.messageType');
		$fastSelect = array_map(function ($item) {
			return $item->objectTypeID;
		}, array_filter($objectTypes, function ($item) {
			return $item->getProcessor()->supportsFastSelect();
		}));

		// Build fast select filter
		$condition = new \wcf\system\database\util\PreparedStatementConditionBuilder();
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
		        FROM     chat".WCF_N."_message
		        ".$condition."
		        ORDER BY messageID ".$sortOrder;
		$statement = WCF::getDB()->prepareStatement($sql, 20);
		$statement->execute($condition->getParameters());
		$messageIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);

		$objectList = new MessageList();
		$objectList->setObjectIDs($messageIDs);
		$objectList->readObjects();
		$objects = $objectList->getObjects();

		$canSeeLog = $room->canSeeLog();
		$messages = array_map(function (Message $item) use ($room) {
			return new ViewableMessage($item, $room);
		}, array_filter($objects, function (Message $message) use ($canSeeLog, $room) {
			if ($this->parameters['inLog'] || $message->isInLog()) {
				return $canSeeLog && $message->getMessageType()->getProcessor()->canSeeInLog($message, $room);
			}
			else {
				return $message->getMessageType()->getProcessor()->canSee($message, $room);
			}
		}));

		$embeddedObjectMessageIDs = array_map(function ($message) {
			return $message->messageID;
		}, array_filter($messages, function ($message) {
			return $message->hasEmbeddedObjects;
		}));

		if (!empty($embeddedObjectMessageIDs)) {
			// load embedded objects
			\wcf\system\message\embedded\object\MessageEmbeddedObjectManager::getInstance()->loadObjects('be.bastelstu.chat.message', $embeddedObjectMessageIDs);
		}

		return [ 'messages' => $messages
		       , 'from'     => $this->parameters['from'] ?: (!empty($objects) ? reset($objects)->messageID : $this->parameters['to'] + 1)
		       , 'to'       => $this->parameters['to'] ?: (!empty($objects) ? end($objects)->messageID : $this->parameters['from'] - 1)
		       ];
	}

	/**
	 * Validates parameters and permissions.
	 */
	public function validatePush() {
		$this->readInteger('roomID');

		$room = RoomCache::getInstance()->getRoom($this->parameters['roomID']);
		if ($room === null) throw new UserInputException('roomID');
		if (!$room->canSee($user = null, $reason)) throw $reason;
		$user = new \chat\data\user\User(WCF::getUser());
		if (!$user->isInRoom($room)) throw new PermissionDeniedException();

		$this->readInteger('commandID');
		$command = CommandCache::getInstance()->getCommand($this->parameters['commandID']);
		if ($command === null) throw new UserInputException('commandID');
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
	public function push() {
		$room = RoomCache::getInstance()->getRoom($this->parameters['roomID']);
		if ($room === null) throw new UserInputException('roomID');

		$command = CommandCache::getInstance()->getCommand($this->parameters['commandID']);
		if ($command === null) throw new UserInputException('commandID');

		$processor = $command->getProcessor();
		$processor->validate($this->parameters['parameters'], $room);
		$processor->execute($this->parameters['parameters'], $room);
	}

	/**
	 * Validates parameters and permissions.
	 */
	public function validatePushAttachment() {
		$this->readInteger('roomID');

		$room = RoomCache::getInstance()->getRoom($this->parameters['roomID']);
		if ($room === null) throw new UserInputException('roomID');
		if (!$room->canSee($user = null, $reason)) throw $reason;
		$user = new \chat\data\user\User(WCF::getUser());
		if (!$user->isInRoom($room)) throw new PermissionDeniedException();

		if (!$room->canWritePublicly(null, $reason)) throw $reason;

		$this->readString('tmpHash');
	}

	/**
	 * Pushes a new attachment into the given room.
	 */
	public function pushAttachment() {
		$objectTypeID = ObjectTypeCache::getInstance()->getObjectTypeIDByName('be.bastelstu.chat.messageType', 'be.bastelstu.chat.messageType.attachment');
		if (!$objectTypeID) throw new \LogicException('Missing object type');

		$room = RoomCache::getInstance()->getRoom($this->parameters['roomID']);
		if ($room === null) throw new UserInputException('roomID');

		$attachmentHandler = new AttachmentHandler('be.bastelstu.chat.message', 0, $this->parameters['tmpHash'], $room->roomID);
		$attachments = $attachmentHandler->getAttachmentList();
		$attachmentIDs = [];
		foreach ($attachments as $attachment) {
			$attachmentIDs[] = $attachment->attachmentID;
		}

		$processor = new \wcf\system\html\input\HtmlInputProcessor();
		$processor->process(implode(' ', array_map(function ($attachmentID) {
			return '[attach='.$attachmentID.',none,true][/attach]';
		}, $attachmentIDs)), 'be.bastelstu.chat.message', 0);

		WCF::getDB()->beginTransaction();
		/** @var Message $message */
		$message = (new MessageAction([ ], 'create', [ 'data' => [ 'roomID'       => $room->roomID
		                                                         , 'userID'       => WCF::getUser()->userID
		                                                         , 'username'     => WCF::getUser()->username
		                                                         , 'time'         => TIME_NOW
		                                                         , 'objectTypeID' => $objectTypeID
		                                                         , 'payload'      => serialize([ 'attachmentIDs' => $attachmentIDs
		                                                                                       , 'message' => $processor->getHtml()
		                                                                                       ])
		                                                         ]
		                                             ]
		                             )
		           )->executeAction()['returnValues'];

		$attachmentHandler->updateObjectID($message->messageID);
		$processor->setObjectID($message->messageID);
		if (\wcf\system\message\embedded\object\MessageEmbeddedObjectManager::getInstance()->registerObjects($processor)) {
			(new MessageEditor($message))->update([
				'hasEmbeddedObjects' => 1
			]);
		}
		WCF::getDB()->commitTransaction();
	}
}
