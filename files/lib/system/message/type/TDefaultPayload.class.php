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

namespace chat\system\message\type;

/**
 * Default implementation for 'getPayload'.
 */
trait TDefaultPayload {
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

		return $parameters['payload'];
	}
}
