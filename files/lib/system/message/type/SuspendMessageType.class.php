<?php

/*
 * Copyright (c) 2010-2024 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2028-01-13
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
 * SuspendMessageType informs about suspensions.
 */
final class SuspendMessageType implements IMessageType
{
    /**
     * @inheritDoc
     */
    public function getJavaScriptModuleName(): string
    {
        return 'Bastelstu.be/Chat/MessageType/Suspend';
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
        unset($payload['roomIDs']);

        $parameters = [
            'message' => $message,
            'user' => $user,
            'payload' => $payload,
        ];
        EventHandler::getInstance()->fireAction($this, 'getPayload', $parameters);

        return $parameters['payload'];
    }

    /**
     * @inheritDoc
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
            'canSee' => \in_array($room->roomID, $message->payload['roomIDs'], true),
        ];
        EventHandler::getInstance()->fireAction($this, 'canSee', $parameters);

        return $parameters['canSee'];
    }

    /**
     * @inheritDoc
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
            'canSee' => \in_array($room->roomID, $message->payload['roomIDs'], true),
        ];
        EventHandler::getInstance()->fireAction($this, 'canSeeInLog', $parameters);

        return $parameters['canSee'];
    }

    /**
     * @inheritDoc
     */
    public function supportsFastSelect(): bool
    {
        return false;
    }
}
