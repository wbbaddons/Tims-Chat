<?php
/*
 * Copyright (c) 2010-2021 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2025-03-05
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
 * ChatUpdateMessageType informs the chat about a back end update.
 */
class ChatUpdateMessageType implements IMessageType {
	use TDefaultPayload;

	/**
	 * @inheritDoc
	 */
	public function getJavaScriptModuleName() {
		return 'Bastelstu.be/Chat/MessageType/ChatUpdate';
	}

	/**
	 * @see	\chat\system\message\type\IMessageType::canSee()
	 */
	public function canSee(Message $message, Room $room, UserProfile $user = null) {
		return true;
	}

	/**
	 * @see	\chat\system\message\type\IMessageType::canSeeInLog()
	 */
	public function canSeeInLog(Message $message, Room $room, UserProfile $user = null) {
		return true;
	}

	/**
	 * @see»\chat\system\message\type\IMessageType::supportsFastSelect()
	 */
	public function supportsFastSelect() {
		return false;
	}
}
