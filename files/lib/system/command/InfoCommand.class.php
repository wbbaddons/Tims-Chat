<?php
/*
 * Copyright (c) 2010-2018 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2022-08-16
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

namespace chat\system\command;

use \chat\data\message\MessageAction;
use \chat\data\room\Room;
use \chat\data\room\RoomCache;
use \wcf\data\user\User;
use \wcf\data\user\UserProfile;

/**
 * The info command shows information about a single user.
 */
class InfoCommand extends AbstractCommand implements ICommand {
	use TNeedsUser;

	/**
	 * @inheritDoc
	 */
	public function getJavaScriptModuleName() {
		return 'Bastelstu.be/Chat/Command/Info';
	}

	/**
	 * @inheritDoc
	 */
	public function validate($parameters, Room $room, UserProfile $user = null) {
		if ($user === null) $user = new UserProfile(\wcf\system\WCF::getUser());

		$this->assertUser($this->assertParameter($parameters, 'username'));
	}

	/**
	 * @inheritDoc
	 */
	public function execute($parameters, Room $room, UserProfile $user = null) {
		if ($user === null) $user = new UserProfile(\wcf\system\WCF::getUser());

		$objectTypeID = $this->getMessageObjectTypeID('be.bastelstu.chat.messageType.info');
		$target = new \chat\data\user\User($this->getUser($this->assertParameter($parameters, 'username')));
		$rooms = array_values(array_map(function ($assoc) {
			$room = RoomCache::getInstance()->getRoom($assoc['roomID']);

			return [ 'title'    => (string) $room
			       , 'roomID'   => $assoc['roomID']
			       , 'lastPush' => $assoc['lastPush']
			       , 'lastPull' => $assoc['lastPull']
			       , 'active'   => $assoc['active']
			       , 'link'     => $room->getLink()
			       ];
		}, array_filter($target->getRoomAssociations(), function ($assoc) {
			return RoomCache::getInstance()->getRoom($assoc['roomID'])->canSee();
		})));

		$payload = [ 'data' => [ 'rooms'  => $rooms
		                       , 'away'   => $target->chatAway
		                       , 'user'   => $target
		                       ]
		           , 'caller' => $user
		           ];

		\wcf\system\event\EventHandler::getInstance()->fireAction($this, 'execute', $payload);

		(new MessageAction([ ], 'create', [ 'data' => [ 'roomID'       => $room->roomID
		                                              , 'userID'       => $user->userID
		                                              , 'username'     => $user->username
		                                              , 'time'         => TIME_NOW
		                                              , 'objectTypeID' => $objectTypeID
		                                              , 'payload'      => serialize($payload['data'])
		                                              ]
		                                  , 'updateTimestamp' => true
		                                  ]
		                  )
		)->executeAction();
	}
}
