<?php
/*
 * Copyright (c) 2010-2018 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2024-11-02
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

namespace chat\data\room;

use \chat\data\user\User as ChatUser;
use \chat\data\message\MessageAction;
use \wcf\data\object\type\ObjectTypeCache;
use \wcf\system\cache\runtime\UserProfileRuntimeCache;
use \wcf\system\database\util\PreparedStatementConditionBuilder;
use \wcf\system\exception\PermissionDeniedException;
use \wcf\system\exception\UserInputException;
use \wcf\system\user\activity\point\UserActivityPointHandler;
use \wcf\system\WCF;

/**
 * Executes chat room-related actions.
 */
class RoomAction extends \wcf\data\AbstractDatabaseObjectAction implements \wcf\data\ISortableAction {
	/**
	 * @inheritDoc
	 */
	protected $permissionsDelete = [ 'admin.chat.canManageRoom' ];

	/**
	 * @inheritDoc
	 */
	protected $permissionsUpdate = [ 'admin.chat.canManageRoom' ];

	/**
	 * Validates parameters and permissions.
	 */
	public function validateJoin() {
		unset($this->parameters['user']);

		$this->readString('sessionID');
		$this->parameters['sessionID'] = pack('H*', str_replace('-', '', $this->parameters['sessionID']));

		$this->readInteger('roomID');

		$room = RoomCache::getInstance()->getRoom($this->parameters['roomID']);
		if ($room === null) throw new UserInputException('roomID');
		if (!$room->canSee($user = null, $reason)) throw $reason;
		if (!$room->canJoin($user = null, $reason)) throw $reason;
	}

	/**
	 * Makes the given user join the current chat room.
	 */
	public function join() {
		$objectTypeID = ObjectTypeCache::getInstance()->getObjectTypeIDByName('be.bastelstu.chat.messageType', 'be.bastelstu.chat.messageType.join');
		if (!$objectTypeID) throw new \LogicException('Missing object type');
		// User cannot be set during an AJAX request, but may be set by Tim’s Chat itself.
		if (!isset($this->parameters['user'])) $this->parameters['user'] = WCF::getUser();
		$user = new ChatUser($this->parameters['user']);

		// Check parameters
		$room = RoomCache::getInstance()->getRoom($this->parameters['roomID']);
		if ($room === null) throw new UserInputException('roomID');
		$sessionID = $this->parameters['sessionID'];
		if (strlen($sessionID) !== 16) throw new UserInputException('sessionID');

		try {
			// Create room_to_user mapping.
			$sql = "INSERT INTO chat".WCF_N."_room_to_user (active, roomID, userID) VALUES (?, ?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([ 0, $room->roomID, $user->userID ]);
		}
		catch (\wcf\system\database\exception\DatabaseException $e) {
			// Ignore if there already is a mapping.
			if ((string) $e->getCode() !== '23000') throw $e;
		}

		try {
			$sql = "INSERT INTO chat".WCF_N."_session (roomID, userID, sessionID, lastRequest) VALUES (?, ?, ?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([ $room->roomID, $user->userID, $sessionID, TIME_NOW ]);
		}
		catch (\wcf\system\database\exception\DatabaseException $e) {
			if ((string) $e->getCode() !== '23000') throw $e;

			throw new UserInputException('sessionID');
		}

		$markAsBack = function () use ($user, $room) {
			$userProfile = new \wcf\data\user\UserProfile($user->getDecoratedObject());
			$package = \wcf\data\package\PackageCache::getInstance()->getPackageByIdentifier('be.bastelstu.chat');
			$command = \chat\data\command\CommandCache::getInstance()->getCommandByPackageAndIdentifier($package, 'back');
			$processor = $command->getProcessor();
			$processor->execute([ ], $room, $userProfile);
		};

		if ($user->chatAway !== null) {
			$markAsBack();
		}

		// Attempt to mark the user as active in the room.
		$sql = "UPDATE chat".WCF_N."_room_to_user SET active = ? WHERE roomID = ? AND userID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([ 1, $room->roomID, $user->userID ]);
		if ($statement->getAffectedRows() === 0) {
			// The User already is inside the room: Nothing to do here.
			return;
		}

		// Update lastPull. This must not be merged into the above query, because of the 'getAffectedRows' check.
		$sql = "UPDATE chat".WCF_N."_room_to_user SET lastPull = ? WHERE roomID = ? AND userID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([ TIME_NOW, $room->roomID, $user->userID ]);

		(new MessageAction([ ], 'create', [ 'data' => [ 'roomID'       => $room->roomID
		                                              , 'userID'       => $user->userID
		                                              , 'username'     => $user->username
		                                              , 'time'         => TIME_NOW
		                                              , 'objectTypeID' => $objectTypeID
		                                              , 'payload'      => serialize([ ])
		                                              ]
		                                  ]
		                  )
		)->executeAction();

		UserActivityPointHandler::getInstance()->fireEvent('be.bastelstu.chat.activityPointEvent.join', 0, $user->userID);
		$pushHandler = \wcf\system\push\PushHandler::getInstance();
		$pushHandler->sendMessage([ 'message' => 'be.bastelstu.chat.join'
		                          , 'target' => 'registered'
		                          ]);
	}

	/**
	 * Validates parameters and permissions.
	 */
	public function validateLeave() {
		unset($this->parameters['user']);

		$this->readString('sessionID');
		$this->parameters['sessionID'] = pack('H*', str_replace('-', '', $this->parameters['sessionID']));

		$this->readInteger('roomID');
		$room = RoomCache::getInstance()->getRoom($this->parameters['roomID']);
		if ($room === null) throw new UserInputException('roomID');
		// Do not check permissions: If the user is not inside the room nothing happens, if he is it
		//                           may lead to a faster eviction of the user.
	}

	/**
	 * Makes the given user leave the current chat room.
	 */
	public function leave() {
		$objectTypeID = ObjectTypeCache::getInstance()->getObjectTypeIDByName('be.bastelstu.chat.messageType', 'be.bastelstu.chat.messageType.leave');
		if ($objectTypeID) {
			// User cannot be set during an AJAX request, but may be set by Tim’s Chat itself.
			if (!isset($this->parameters['user'])) $this->parameters['user'] = WCF::getUser();
			$user = new ChatUser($this->parameters['user']);

			$room = RoomCache::getInstance()->getRoom($this->parameters['roomID']);
			if ($room === null) throw new UserInputException('roomID');

			$sessionID = null;
			if (isset($this->parameters['sessionID'])) {
				$sessionID = $this->parameters['sessionID'];
				if (strlen($sessionID) !== 16) throw new UserInputException('sessionID');
			}

			// Delete session.
			$condition = new \wcf\system\database\util\PreparedStatementConditionBuilder();
			$condition->add('roomID = ?', [ $room->roomID ]);
			$condition->add('userID = ?', [ $user->userID ]);
			if ($sessionID !== null) {
				$condition->add('sessionID = ?', [ $sessionID ]);
			}
			$sql = "DELETE FROM chat".WCF_N."_session
			       ".$condition;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($condition->getParameters());
			if ($statement->getAffectedRows() === 0) {
				throw new UserInputException('sessionID');
			}

			try {
				$commited = false;
				WCF::getDB()->beginTransaction();

				// Check whether we deleted the last session.
				$sql = "SELECT COUNT(*)
				        FROM   chat".WCF_N."_session
				        WHERE      roomID = ?
				               AND userID = ?";
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute([ $room->roomID, $user->userID ]);

				// We did not: Nothing to do here.
				if ($statement->fetchColumn()) return;

				// Mark the user as inactive.
				$sql = "UPDATE chat".WCF_N."_room_to_user SET active = ? WHERE roomID = ? AND userID = ?";
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute([ 0, $room->roomID, $user->userID ]);
				if ($statement->getAffectedRows() === 0) throw new \LogicException('Unreachable');

				WCF::getDB()->commitTransaction();
				$commited = true;
			}
			finally {
				if (!$commited) WCF::getDB()->rollBackTransaction();
			}

			(new MessageAction([ ], 'create', [ 'data' => [ 'roomID'       => $room->roomID
			                                              , 'userID'       => $user->userID
			                                              , 'username'     => $user->username
			                                              , 'time'         => TIME_NOW
			                                              , 'objectTypeID' => $objectTypeID
			                                              , 'payload'      => serialize([ ])
			                                              ]
			                                  ]
			                  )
			)->executeAction();

			$pushHandler = \wcf\system\push\PushHandler::getInstance();
			$pushHandler->sendMessage([ 'message' => 'be.bastelstu.chat.leave'
			                          , 'target' => 'registered'
			                          ]);
		}
		else {
			throw new \LogicException('Missing object type');
		}
	}

	/**
	 * Validates parameters and permissions.
	 */
	public function validateGetUsers() {
		if (empty($this->getObjects())) {
			$this->readObjects();
		}
		if (count($this->getObjects()) !== 1) {
			throw new UserInputException('objectIDs');
		}

		$room = $this->getObjects()[0];

		$user = new ChatUser(WCF::getUser());
		if (!$user->isInRoom($room->getDecoratedObject())) throw new PermissionDeniedException();
	}

	/**
	 * Returns the userIDs of the users in this room.
	 */
	public function getUsers() {
		if (empty($this->getObjects())) {
			$this->readObjects();
		}
		if (count($this->getObjects()) !== 1) {
			throw new UserInputException('objectIDs');
		}

		$room = $this->getObjects()[0];

		$users = (new \chat\data\user\UserAction([ ], 'getUsersByID', [
			'userIDs' => array_keys($room->getUsers())
		]))->executeAction()['returnValues'];

		$users = array_map(function (array $user) use ($room) {
			$userProfile = UserProfileRuntimeCache::getInstance()->getObject($user['userID']);
			if (!isset($user['permissions'])) $user['permissions'] = [];
			$user['permissions']['canWritePublicly'] = $room->canWritePublicly($userProfile);

			return $user;
		}, $users);
		
		\wcf\system\event\EventHandler::getInstance()->fireAction($this, 'getUsers', $users);

		return $users;
	}

	/**
	 * @inheritDoc
	 */
	public function validateUpdatePosition() {
		// validate permissions
		if (is_array($this->permissionsUpdate) && !empty($this->permissionsUpdate)) {
			WCF::getSession()->checkPermissions($this->permissionsUpdate);
		}
		else {
			throw new PermissionDeniedException();
		}

		$this->readIntegerArray('structure', false, 'data');

		$roomList = new RoomList();
		$roomList->readObjects();

		foreach ($this->parameters['data']['structure'][0] as $roomID) {
			$room = $roomList->search($roomID);
			if ($room === null) throw new UserInputException('structure');
		}
	}

	/**
	 * @inheritDoc
	 */
	public function updatePosition() {
		$roomList = new RoomList();
		$roomList->readObjects();

		$i = 0;
		WCF::getDB()->beginTransaction();
		foreach ($this->parameters['data']['structure'][0] as $roomID) {
			$room = $roomList->search($roomID);
			if ($room === null) continue;

			$editor = new RoomEditor($room);
			$editor->update([ 'position' => $i++ ]);
		}
		WCF::getDB()->commitTransaction();
	}

	/**
	 * Validates permissions.
	 */
	public function validateGetBoxRoomList() {
		if (!\chat\data\room\Room::canSeeAny()) throw new \wcf\system\exception\PermissionDeniedException();

		$this->readBoolean('isSidebar', true);
		$this->readBoolean('skipEmptyRooms', true);
		$this->readInteger('activeRoomID', true);

		unset($this->parameters['boxController']);
		$this->readInteger('boxID', true);
		if ($this->parameters['boxID']) {
			$box = new \wcf\data\box\Box($this->parameters['boxID']);
			if ($box->boxID) {
				$this->parameters['boxController'] = $box->getController();
				if ($this->parameters['boxController'] instanceof \chat\system\box\RoomListBoxController) {
					// all checks passed, end validation; otherwise throw the exception below
					return;
				}
			}

			throw new UserInputException('boxID');
		}
	}

	/**
	 * Returns dashboard roomlist.
	 */
	public function getBoxRoomList() {
		if (isset($this->parameters['boxController'])) {
			$this->parameters['boxController']->setActiveRoomID($this->parameters['activeRoomID']);

			return [ 'template' => $this->parameters['boxController']->getContent() ];
		}

		// Fetch all rooms, the templates have filtering in place
		$rooms = RoomCache::getInstance()->getRooms();

		$template = 'boxRoomList'.($this->parameters['isSidebar'] ? 'Sidebar' : '');

		\wcf\system\WCF::getTPL()->assign([ 'boxRoomList'    => $rooms
		                                  , 'skipEmptyRooms' => $this->parameters['skipEmptyRooms']
		                                  , 'activeRoomID'   => $this->parameters['activeRoomID']
		                                  ]);

		return [ 'template' => \wcf\system\WCF::getTPL()->fetch($template, 'chat') ];
	}
}
