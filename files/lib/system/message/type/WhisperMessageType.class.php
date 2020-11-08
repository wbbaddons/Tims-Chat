<?php
/*
 * Copyright (c) 2010-2018 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2024-11-08
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
 * WhisperMessageType represents a whispered message.
 */
class WhisperMessageType implements IMessageType {
	/**
	 * HtmlOutputProcessor to use.
	 * @var	\wcf\system\html\output\HtmlOutputProcessor
	 */
	protected $processor = null;

	public function __construct() {
		$this->processor = new \wcf\system\html\output\HtmlOutputProcessor();
	}

	/**
	 * @inheritDoc
	 */
	public function getJavaScriptModuleName() {
		return 'Bastelstu.be/Chat/MessageType/Whisper';
	}

	/**
	 * @see	\chat\system\message\type\IMessageType::getPayload()
	 */
	public function getPayload(Message $message, UserProfile $user = null) {
		if ($user === null) $user = new UserProfile(\wcf\system\WCF::getUser());

		$payload = $message->payload;
		$payload['formattedMessage'] = null;
		$payload['plaintextMessage'] = null;

		$parameters = [ 'message' => $message
		              , 'user' => $user
		              , 'payload' => $payload
		              ];
		\wcf\system\event\EventHandler::getInstance()->fireAction($this, 'getPayload', $parameters);

		if ($parameters['payload']['formattedMessage'] === null) {
			$this->processor->process($parameters['payload']['message'], 'be.bastelstu.chat.message', $message->messageID);
			$parameters['payload']['formattedMessage'] = $this->processor->getHtml();
		}

		if ($parameters['payload']['plaintextMessage'] === null) {
			$this->processor->setOutputType('text/plain');
			$this->processor->process($parameters['payload']['message'], 'be.bastelstu.chat.message', $message->messageID);
			$parameters['payload']['plaintextMessage'] = $this->processor->getHtml();
		}

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
