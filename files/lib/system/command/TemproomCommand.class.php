<?php
/*
 * Copyright (c) 2010-2021 Tim Düsterhus.
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

namespace chat\system\command;

use \chat\data\message\MessageAction;
use \chat\data\room\Room;
use \wcf\data\user\UserProfile;
use \wcf\system\exception\PermissionDeniedException;
use \wcf\system\exception\UserInputException;
use \wcf\system\WCF;

/**
 * The temproom command allows a user to manage temporary rooms.
 */
class TemproomCommand extends AbstractCommand implements ICommand {
	use TNeedsUser;

	/**
	 * @inheritDoc
	 */
	public function getJavaScriptModuleName() {
		return 'Bastelstu.be/Chat/Command/Temproom';
	}

	/**
	 * @inheritDoc
	 */
	public function validate($parameters, Room $room, UserProfile $user = null) {
		if ($user === null) $user = new UserProfile(WCF::getUser());

		switch ($this->assertParameter($parameters, 'type')) {
			case 'create':
				if (!$user->getPermission('user.chat.canTemproom')) throw new PermissionDeniedException();
			break;
			case 'invite':
				if (!$room->isTemporary) throw new UserInputException('message', WCF::getLanguage()->getDynamicVariable('chat.error.notInTemproom'));
				if ($room->ownerID !== $user->userID) throw new PermissionDeniedException();

				$recipient = new UserProfile($this->assertUser($this->assertParameter($parameters, 'username')));
				if ($recipient->isIgnoredUser($user->userID)) throw new UserInputException('message', WCF::getLanguage()->getDynamicVariable('chat.error.userIgnoresYou', [ 'user' => $recipient ]));
			break;
			case 'delete':
				if (!$room->isTemporary) throw new UserInputException('message', WCF::getLanguage()->getDynamicVariable('chat.error.notInTemproom'));
				if ($room->ownerID !== $user->userID) throw new PermissionDeniedException();
			break;
			default:
				throw new UserInputException('message');
		}
	}

	/**
	 * @inheritDoc
	 */
	public function execute($parameters, Room $room, UserProfile $user = null) {
		if ($user === null) $user = new UserProfile(WCF::getUser());

		switch ($this->assertParameter($parameters, 'type')) {
			case 'create':
				$fields = [ 'title' => WCF::getLanguage()->getDynamicVariable('chat.room.temporary.blueprint', [ 'user' => $user ])
				          , 'topic' => ''
				          , 'position' => 999
				          , 'isTemporary' => true
				          , 'ownerID' => $user->userID
				          ];

				WCF::getDB()->beginTransaction();
				// create room
				$tempRoom = (new \chat\data\room\RoomAction([], 'create', [ 'data' => $fields ]))->executeAction()['returnValues'];
				$objectTypeID = $this->getMessageObjectTypeID('be.bastelstu.chat.messageType.temproomCreated');
				(new MessageAction([ ], 'create', [ 'data' => [ 'roomID'       => $room->roomID
				                                              , 'userID'       => $user->userID
				                                              , 'username'     => $user->username
				                                              , 'time'         => TIME_NOW
				                                              , 'objectTypeID' => $objectTypeID
				                                              , 'payload'      => serialize([ 'room' => $tempRoom ])
				                                              ]
				                                  , 'updateTimestamp' => true
				                                  ]
				                  )
				)->executeAction();
				WCF::getDB()->commitTransaction();
				return;
			case 'invite':
				$recipient = $this->getUser($this->assertParameter($parameters, 'username'));
				WCF::getDB()->beginTransaction();
				try {
					$sql = "INSERT INTO chat1_room_temporary_invite
					               (userID, roomID)
					        VALUES (?, ?)";
					$statement = WCF::getDB()->prepare($sql);
					$statement->execute([ $recipient->userID, $room->roomID ]);
				}
				catch (\wcf\system\database\DatabaseException $e) {
					WCF::getDB()->rollBackTransaction();
					// Duplicate key errors don't cause harm.
					if ((string) $e->getCode() !== '23000') throw $e;
					return;
				}

				$objectTypeID = $this->getMessageObjectTypeID('be.bastelstu.chat.messageType.temproomInvited');
				(new MessageAction([ ], 'create', [ 'data' => [ 'roomID'       => $room->roomID
				                                              , 'userID'       => $user->userID
				                                              , 'username'     => $user->username
				                                              , 'time'         => TIME_NOW
				                                              , 'objectTypeID' => $objectTypeID
				                                              , 'payload'      => serialize([ 'recipient'     => $recipient->userID
				                                                                            , 'recipientName' => $recipient->username
				                                                                            ])
				                                              ]
				                                  , 'updateTimestamp' => true
				                                  ]
				                  )
				)->executeAction();
				WCF::getDB()->commitTransaction();

				return;
			case 'delete':
				(new \chat\data\room\RoomAction([ $room ], 'delete'))->executeAction();
				return;
			default:
				throw new UserInputException('message');
		}
	}
}
