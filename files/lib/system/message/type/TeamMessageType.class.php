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
 * TeamMessageType represents a team internal message.
 */
final class TeamMessageType implements IMessageType, IDeletableMessageType
{
    /**
     * @var PlainMessageType
     */
    protected $plainMessageType;

    public function __construct()
    {
        $this->plainMessageType = new PlainMessageType();
    }

    /**
     * @inheritDoc
     */
    public function getJavaScriptModuleName(): string
    {
        return 'Bastelstu.be/Chat/MessageType/Team';
    }

    /**
     * @inheritDoc
     */
    public function getPayload(Message $message, ?UserProfile $user = null)
    {
        return $this->plainMessageType->getPayload($message, $user);
    }

    /**
     * @inheritDoc
     */
    public function canDelete(Message $message, ?UserProfile $user = null): bool
    {
        if ($user === null) {
            $user = new UserProfile(WCF::getUser());
        }

        return !!$user->getPermission('mod.chat.canDelete');
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
            'canSee' => !!$user->getPermission('mod.chat.canTeam'),
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
            'canSee' => !!$user->getPermission('mod.chat.canTeam'),
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
