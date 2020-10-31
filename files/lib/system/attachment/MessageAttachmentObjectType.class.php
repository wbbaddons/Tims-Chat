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

namespace chat\system\attachment;

use \chat\data\message\Message;
use \chat\data\message\MessageList;
use \chat\data\room\RoomCache;
use \wcf\system\WCF;

/**
 * Attachment object type implementation for messages.
 */
class MessageAttachmentObjectType extends \wcf\system\attachment\AbstractAttachmentObjectType {
	/**
	 * @inheritDoc
	 */
	public function canDownload($objectID) {
		if ($objectID) {
			$message = new Message($objectID);

			if ($message->getMessageType()->objectType !== 'be.bastelstu.chat.messageType.attachment') {
				throw new \LogicException('Unreachable');
			}
			$room = $message->getRoom();

			return $room->canSee();
		}

		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function canUpload($objectID, $parentObjectID = 0) {
		if ($objectID) {
			return false;
		}

		if (!WCF::getSession()->getPermission('user.chat.canAttach')) {
			return false;
		}

		$room = null;
		if ($parentObjectID) {
			$room = RoomCache::getInstance()->getRoom($parentObjectID);
		}

		if ($room !== null) {
			return $room->canSee();
		}

		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function canDelete($objectID) {
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function getMaxCount() {
		return 1;
	}

	/**
	 * @inheritDoc
	 */
	public function cacheObjects(array $objectIDs) {
		$messageList = new MessageList();
		$messageList->setObjectIDs($objectIDs);
		$messageList->readObjects();

		foreach ($messageList->getObjects() as $objectID => $object) {
			$this->cachedObjects[$objectID] = $object;
		}
	}
}
