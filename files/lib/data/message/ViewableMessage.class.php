<?php

/*
 * Copyright (c) 2010-2022 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2026-03-10
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

namespace chat\data\message;

use chat\data\room\Room;
use chat\page\LogPage;
use wcf\data\DatabaseObjectDecorator;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Represents a chat message.
 */
class ViewableMessage extends DatabaseObjectDecorator implements \JsonSerializable
{
    protected static $baseClass = Message::class;

    protected $room;

    public function __construct(Message $message, Room $room)
    {
        parent::__construct($message);

        $this->room = $room;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        $link = LinkHandler::getInstance()->getControllerLink(
            LogPage::class,
            [
                'messageid' => $this->messageID,
                'object' => $this->room,
            ]
        );

        if ($this->isDeleted) {
            $payload = false;
            $objectType = 'be.bastelstu.chat.messageType.tombstone';
        } else {
            $payload = $this->getMessageType()->getProcessor()->getPayload($this->getDecoratedObject());
            $objectType = $this->getMessageType()->objectType;
        }

        return [
            'messageID' => $this->messageID,
            'userID' => $this->userID,
            'username' => $this->username,
            'time' => $this->time,
            'payload' => $payload,
            'objectType' => $objectType,
            'link' => $link,
            'isIgnored' => WCF::getUserProfileHandler()->isIgnoredUser($this->userID),
            'isDeleted' => (bool)$this->isDeleted,
        ];
    }
}
