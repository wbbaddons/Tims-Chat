<?php

/*
 * Copyright (c) 2010-2024 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2028-06-15
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

namespace chat\system\message\type;

use chat\data\message\Message;
use chat\data\room\Room;
use wcf\data\user\UserProfile;
use wcf\system\event\EventHandler;
use wcf\system\WCF;

/**
 * Adds a default canSee implementation that checks whether the message belongs to the user's active room.
 */
trait TCanSeeInSameRoom
{
    /**
     * @see \chat\system\message\type\IMessageType::canSee()
     */
    public function canSee(Message $message, Room $room, ?UserProfile $user = null): bool
    {
        if ($user === null) {
            $user = new UserProfile(WCF::getUser());
        }

        $parameters = [
            'message' => $message,
            'room' => $room,
            'user' => $user,
            'canSee' => $message->getRoom()->roomID === $room->roomID,
        ];
        EventHandler::getInstance()->fireAction($this, 'canSee', $parameters);

        return $parameters['canSee'];
    }

    /**
     * @see \chat\system\message\type\IMessageType::canSeeInLog()
     */
    public function canSeeInLog(Message $message, Room $room, ?UserProfile $user = null): bool
    {
        if ($user === null) {
            $user = new UserProfile(WCF::getUser());
        }

        $parameters = [
            'message' => $message,
            'room' => $room,
            'user' => $user,
            'canSee' => $message->getRoom()->roomID === $room->roomID,
        ];
        EventHandler::getInstance()->fireAction($this, 'canSeeInLog', $parameters);

        return $parameters['canSee'];
    }

    /**
     * @see \chat\system\message\type\IMessageType::supportsFastSelect()
     */
    public function supportsFastSelect(): bool
    {
        $parameters = [
            'result' => true,
        ];
        EventHandler::getInstance()->fireAction($this, 'supportsFastSelect', $parameters);

        return $parameters['result'];
    }
}
