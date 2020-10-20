<?php
/*
 * Copyright (c) 2010-2018 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2024-10-20
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

namespace chat\system\command;

use \chat\data\message\MessageAction;
use \chat\data\message\MessageEditor;
use \chat\data\room\Room;
use \wcf\data\user\UserProfile;
use \wcf\system\exception\PermissionDeniedException;
use \wcf\system\WCF;

/**
 * BroadcastCommand sends a broadcast into all channels.
 */
class BroadcastCommand extends AbstractInputProcessedCommand implements ICommand {
	/**
	 * @inheritDoc
	 */
	public function getJavaScriptModuleName() {
		return 'Bastelstu.be/Chat/Command/Broadcast';
	}

	/**
	 * @inheritDoc
	 */
	public function isAvailable(Room $room, UserProfile $user = null) {
		if ($user === null) $user = new UserProfile(WCF::getUser());
		return $user->getPermission('mod.chat.canBroadcast');
	}

	/**
	 * @inheritDoc
	 */
	public function validate($parameters, Room $room, UserProfile $user = null) {
		if ($user === null) $user = new UserProfile(\wcf\system\WCF::getUser());

		if (!$user->getPermission('mod.chat.canBroadcast')) throw new PermissionDeniedException();

		$this->setText($this->assertParameter($parameters, 'text'));
		$this->validateText();
	}

	/**
	 * @inheritDoc
	 */
	public function execute($parameters, Room $room, UserProfile $user = null) {
		if ($user === null) $user = new UserProfile(\wcf\system\WCF::getUser());

		$objectTypeID = $this->getMessageObjectTypeID('be.bastelstu.chat.messageType.broadcast');
		$this->setText($this->assertParameter($parameters, 'text'));

		WCF::getDB()->beginTransaction();
		$message = (new MessageAction([ ], 'create', [ 'data' => [ 'roomID'       => $room->roomID
		                                                         , 'userID'       => $user->userID
		                                                         , 'username'     => $user->username
		                                                         , 'time'         => TIME_NOW
		                                                         , 'objectTypeID' => $objectTypeID
		                                                         , 'payload'      => serialize([ 'message' => $this->processor->getHtml() ])
		                                                         ]
		                                             , 'updateTimestamp' => true
		                                             ]
		                             )
		           )->executeAction()['returnValues'];

		$this->processor->setObjectID($message->messageID);
		if (\wcf\system\message\embedded\object\MessageEmbeddedObjectManager::getInstance()->registerObjects($this->processor)) {
			(new MessageEditor($message))->update([
				'hasEmbeddedObjects' => 1
			]);
		}
		WCF::getDB()->commitTransaction();
	}
}
