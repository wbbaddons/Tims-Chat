<?php
/*
 * Copyright (c) 2010-2018 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2024-11-01
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

namespace chat\system\message\type;

/**
 * MeMessageType represents an action message.
 */
class MeMessageType implements IMessageType, IDeletableMessageType {
	use TCanSeeInSameRoom;
	use TDefaultPayload;

	/**
	 * @inheritDoc
	 */
	public function getJavaScriptModuleName() {
		return 'Bastelstu.be/Chat/MessageType/Me';
	}

	/**
	 * @inheritDoc
	 */
	public function canDelete(\chat\data\message\Message $message, \wcf\data\user\UserProfile $user = null) {
		if ($user === null) $user = new \wcf\data\user\UserProfile(\wcf\system\WCF::getUser());

		return $user->getPermission('mod.chat.canDelete');
	}
}
