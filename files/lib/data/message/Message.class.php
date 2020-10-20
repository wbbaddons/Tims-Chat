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

namespace chat\data\message;

/**
 * Represents a chat message.
 */
class Message extends \wcf\data\DatabaseObject {
	/**
	 * @inheritDoc
	 */
	protected function handleData($data) {
		parent::handleData($data);

		$this->data['payload'] = @unserialize($this->data['payload']);
		if (!is_array($this->data['payload'])) {
			$this->data['payload'] = [ ];
		}
	}

	/**
	 * Returns whether this message already is inside the log.
	 *
	 * @return	boolean
	 */
	public function isInLog() {
		return $this->time < (TIME_NOW - CHAT_ARCHIVE_AFTER);
	}

	/**
	 * Returns the message type object of this message.
	 *
	 * @return	\wcf\data\object\type\ObjectType
	 */
	public function getMessageType() {
		return \wcf\data\object\type\ObjectTypeCache::getInstance()->getObjectType($this->objectTypeID);
	}

	/**
	 * Returns the chat room that contains this message.
	 *
	 * @return	\chat\data\room\Room
	 */
	public function getRoom() {
		return \chat\data\room\RoomCache::getInstance()->getRoom($this->roomID);
	}
}
