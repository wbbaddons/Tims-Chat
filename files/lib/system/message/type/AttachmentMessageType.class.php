<?php
/*
 * Copyright (c) 2010-2020 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2024-10-31
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

		$parameters = [ 'message' => $message
		              , 'user'    => $user
		              , 'payload' => $payload
		              ];
		\wcf\system\event\EventHandler::getInstance()->fireAction($this, 'getPayload', $parameters);

		// TODO

		return $parameters['payload'];
	}
}
