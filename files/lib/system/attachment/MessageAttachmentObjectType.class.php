<?php

/*
 * Copyright (c) 2010-2022 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2026-03-04
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

namespace chat\system\attachment;

use chat\data\message\Message;
use chat\data\message\MessageList;
use chat\data\room\RoomCache;
use wcf\system\attachment\AbstractAttachmentObjectType;
use wcf\system\WCF;
use wcf\util\ArrayUtil;

/**
 * Attachment object type implementation for messages.
 */
final class MessageAttachmentObjectType extends AbstractAttachmentObjectType
{
    /**
     * @inheritDoc
     */
    public function canDownload($objectID): bool
    {
        if ($objectID) {
            $message = new Message($objectID);

            \assert($message->getMessageType()->objectType === 'be.bastelstu.chat.messageType.attachment');
            $room = $message->getRoom();

            return $room->canSee();
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function canUpload($objectID, $parentObjectID = 0): bool
    {
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
    public function canDelete($objectID): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function getMaxCount(): int
    {
        return 1;
    }

    /**
     * @inheritDoc
     */
    public function getMaxSize(): int
    {
        return (int)WCF::getSession()->getPermission('user.chat.attachment.maxSize');
    }

    /**
     * @inheritDoc
     */
    public function getAllowedExtensions()
    {
        return ArrayUtil::trim(\explode(
            "\n",
            WCF::getSession()->getPermission('user.chat.attachment.allowedExtensions')
        ));
    }

    /**
     * @inheritDoc
     */
    public function cacheObjects(array $objectIDs)
    {
        $messageList = new MessageList();
        $messageList->setObjectIDs($objectIDs);
        $messageList->readObjects();

        foreach ($messageList->getObjects() as $objectID => $object) {
            $this->cachedObjects[$objectID] = $object;
        }
    }
}
