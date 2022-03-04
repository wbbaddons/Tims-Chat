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

namespace chat\data\message;

use chat\data\room\Room;
use chat\data\room\RoomCache;
use wcf\data\DatabaseObject;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;

/**
 * Represents a chat message.
 *
 * @property-read   integer $messageID
 * @property-read   integer $time
 * @property-read   integer $roomID
 * @property-read   integer $userID
 * @property-read   string  $username
 * @property-read   integer $objectTypeID
 * @property-read   mixed   $payload
 * @property-read   integer $hasEmbeddedObjects
 * @property-read   integer $isDeleted
 */
class Message extends DatabaseObject
{
    /**
     * @inheritDoc
     */
    protected function handleData($data)
    {
        parent::handleData($data);

        $this->data['payload'] = @\unserialize($this->data['payload']);
        if (!\is_array($this->data['payload'])) {
            $this->data['payload'] = [ ];
        }
    }

    /**
     * Returns whether this message already is inside the log.
     */
    public function isInLog(): bool
    {
        return $this->time < (TIME_NOW - CHAT_ARCHIVE_AFTER);
    }

    /**
     * Returns the message type object of this message.
     */
    public function getMessageType(): ObjectType
    {
        return ObjectTypeCache::getInstance()->getObjectType($this->objectTypeID);
    }

    /**
     * Returns the chat room that contains this message.
     */
    public function getRoom(): Room
    {
        return RoomCache::getInstance()->getRoom($this->roomID);
    }
}
