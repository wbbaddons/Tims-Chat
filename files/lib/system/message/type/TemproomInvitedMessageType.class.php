<?php
/*
 * Copyright (c) 2010-2018 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2024-03-25
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

namespace chat\system\message\type;

use \chat\data\message\Message;
use \chat\data\room\Room;
use \wcf\data\user\UserProfile;

/**
 * TemproomInvitedMessageType informs a user that they were invited to a temporary room.
 */
class TemproomInvitedMessageType implements IMessageType {
	/**
	 * @inheritDoc
	 */
	public function getJavaScriptModuleName() {
		return 'Bastelstu.be/Chat/MessageType/TemproomInvited';
	}

	/**
	 * @inheritDoc
	 */
	public function getPayload(Message $message, UserProfile $user = null) {
		if ($user === null) $user = new \wcf\data\user\UserProfile(\wcf\system\WCF::getUser());

		$payload = $message->payload;
		$room = $message->getRoom();
		$payload['room'] = [ 'roomID' => $room->roomID
		                   , 'title' => $room->title
		                   , 'link' => $room->getLink()
		                   ];

		$parameters = [ 'message' => $message
		              , 'user'    => $user
		              , 'payload' => $payload
		              ];
		\wcf\system\event\EventHandler::getInstance()->fireAction($this, 'getPayload', $parameters);

		return $parameters['payload'];
	}

	/**
	 * @see	\chat\system\message\type\IMessageType::canSee()
	 */
	public function canSee(Message $message, Room $room, UserProfile $user = null) {
		if ($user === null) $user = new UserProfile(\wcf\system\WCF::getUser());

		$parameters = [ 'message' => $message
		              , 'room' => $room
		              , 'user' => $user
		              , 'canSee' => $user->userID === $message->userID || $user->userID === $message->payload['recipient']
		              ];
		\wcf\system\event\EventHandler::getInstance()->fireAction($this, 'canSee', $parameters);

		return $parameters['canSee'];
	}

	/**
	 * @see	\chat\system\message\type\IMessageType::canSeeInLog()
	 */
	public function canSeeInLog(Message $message, Room $room, UserProfile $user = null) {
		if ($user === null) $user = new UserProfile(\wcf\system\WCF::getUser());

		$parameters = [ 'message' => $message
		              , 'room' => $room
		              , 'user' => $user
		              , 'canSee' => false
		              ];
		\wcf\system\event\EventHandler::getInstance()->fireAction($this, 'canSeeInLog', $parameters);

		return $parameters['canSee'];
	}

	/**
	 * @see»\chat\system\message\type\IMessageType::supportsFastSelect()
	 */
	public function supportsFastSelect() {
		$parameters = [ 'result' => false ];
		\wcf\system\event\EventHandler::getInstance()->fireAction($this, 'supportsFastSelect', $parameters);

		return $parameters['result'];
	}
}
