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

namespace chat\system\message\type;

use chat\data\message\Message;
use chat\data\room\RoomCache;
use wcf\data\user\UserProfile;
use wcf\system\event\EventHandler;
use wcf\system\WCF;

/**
 * WhereMessageType represents the reply to WhereCommand.
 */
final class WhereMessageType implements IMessageType
{
    use TCanSeeCreator;

    /**
     * @inheritDoc
     */
    public function getJavaScriptModuleName()
    {
        return 'Bastelstu.be/Chat/MessageType/Where';
    }

    /**
     * @inheritDoc
     */
    public function getPayload(Message $message, ?UserProfile $user = null)
    {
        if ($user === null) {
            $user = new UserProfile(WCF::getUser());
        }

        $payload = $message->payload;
        $payload = \array_map(static function ($item) {
            $room = RoomCache::getInstance()->getRoom($item['roomID']);
            $item['room'] = [
                'roomID' => $room->roomID,
                'title' => $room->title,
                'link' => $room->getLink(),
            ];

            return $item;
        }, $payload);

        $parameters = [
            'message' => $message,
            'user' => $user,
            'payload' => $payload,
        ];
        EventHandler::getInstance()->fireAction($this, 'getPayload', $parameters);

        return $parameters['payload'];
    }
}
