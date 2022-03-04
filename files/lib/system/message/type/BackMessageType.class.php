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
use chat\data\room\Room;
use wcf\data\user\UserProfile;
use wcf\system\event\EventHandler;
use wcf\system\WCF;

/**
 * BackMessageType represents a notice that a user now is now back.
 */
class BackMessageType implements IMessageType
{
    use TDefaultPayload;

    /**
     * @inheritDoc
     */
    public function getJavaScriptModuleName()
    {
        return 'Bastelstu.be/Chat/MessageType/Back';
    }

    /**
     * @inheritDoc
     */
    public function canSee(Message $message, Room $room, ?UserProfile $user = null)
    {
        if ($user === null) {
            $user = new UserProfile(WCF::getUser());
        }

        $roomIDs = \array_map(static function ($item) {
            return $item['roomID'];
        }, $message->payload['rooms']);

        $parameters = [
            'message' => $message,
            'room' => $room,
            'user' => $user,
            'canSee' => \in_array($room->roomID, $roomIDs, true),
        ];
        EventHandler::getInstance()->fireAction($this, 'canSee', $parameters);

        return $parameters['canSee'];
    }

    /**
     * @inheritDoc
     */
    public function canSeeInLog(Message $message, Room $room, ?UserProfile $user = null)
    {
        if ($user === null) {
            $user = new UserProfile(WCF::getUser());
        }

        $roomIDs = \array_map(static function ($item) {
            return $item['roomID'];
        }, $message->payload['rooms']);

        $parameters = [
            'message' => $message,
            'room' => $room,
            'user' => $user,
            'canSee' => \in_array($room->roomID, $roomIDs, true),
        ];
        EventHandler::getInstance()->fireAction($this, 'canSeeInLog', $parameters);

        return $parameters['canSee'];
    }

    /**
     * @inheritDoc
     */
    public function supportsFastSelect()
    {
        return false;
    }
}
