<?php
/*
 * Copyright (c) 2010-2020 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2024-11-20
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

namespace chat\system\message\type;

/**
 * AttachmentMessageType represents a message with an attached file.
 */
class AttachmentMessageType implements IMessageType, IDeletableMessageType {
	use TCanSeeInSameRoom;

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
		return 'Bastelstu.be/Chat/MessageType/Plain';
	}

	/**
	 * @inheritDoc
	 */
	public function canDelete(\chat\data\message\Message $message, \wcf\data\user\UserProfile $user = null) {
		if ($user === null) $user = new \wcf\data\user\UserProfile(\wcf\system\WCF::getUser());

		return $user->getPermission('mod.chat.canDelete');
	}

	/**
	 * @see	\chat\system\message\type\IMessageType::getPayload()
	 */
	public function getPayload(\chat\data\message\Message $message, \wcf\data\user\UserProfile $user = null) {
		if ($user === null) $user = new \wcf\data\user\UserProfile(\wcf\system\WCF::getUser());

		$payload = $message->payload;
		$payload['formattedMessage'] = null;
		$payload['plaintextMessage'] = null;

		$parameters = [ 'message' => $message
		              , 'user'    => $user
		              , 'payload' => $payload
		              ];
		\wcf\system\event\EventHandler::getInstance()->fireAction($this, 'getPayload', $parameters);

		if ($parameters['payload']['formattedMessage'] === null) {
			$this->processor->setOutputType('text/html');
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
}
