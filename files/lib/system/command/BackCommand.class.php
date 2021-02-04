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

namespace chat\system\command;

use \chat\data\message\MessageAction;
use \chat\data\room\Room;
use \wcf\data\user\UserProfile;
use \wcf\system\exception\PermissionDeniedException;
use \wcf\system\WCF;

/**
 * The back command marks the user as being back.
 */
class BackCommand extends AbstractCommand implements ICommand {
	/**
	 * @inheritDoc
	 */
	public function allowWithoutTrigger() {
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function getJavaScriptModuleName() {
		return 'Bastelstu.be/Chat/Command/Back';
	}

	/**
	 * @inheritDoc
	 */
	public function validate($parameters, Room $room, UserProfile $user = null) {
		if ($user === null) $user = new UserProfile(WCF::getUser());

		if ($user->chatAway === null) throw new PermissionDeniedException();
	}

	/**
	 * @inheritDoc
	 */
	public function execute($parameters, Room $room, UserProfile $user = null) {
		if ($user === null) $user = new UserProfile(WCF::getUser());

		$objectTypeID = $this->getMessageObjectTypeID('be.bastelstu.chat.messageType.back');
		$rooms = array_map(function (Room $room) use ($user) {
			return [ 'roomID'   => $room->roomID
			       , 'isSilent' => !$room->canWritePublicly($user)
			       ];
		}, (new \chat\data\user\User($user->getDecoratedObject()))->getRooms());

		WCF::getDB()->beginTransaction();
		$editor = new \wcf\data\user\UserEditor($user->getDecoratedObject());
		$editor->update([ 'chatAway' => null ]);

		(new MessageAction([ ], 'create', [ 'data' => [ 'roomID'       => $room->roomID
		                                              , 'userID'       => $user->userID
		                                              , 'username'     => $user->username
		                                              , 'time'         => TIME_NOW
		                                              , 'objectTypeID' => $objectTypeID
		                                              , 'payload'      => serialize([ 'rooms'   => array_values($rooms) ])
		                                              ]
		                                  , 'updateTimestamp' => true
		                                  ]
		                  )
		)->executeAction();
		WCF::getDB()->commitTransaction();
	}
}
