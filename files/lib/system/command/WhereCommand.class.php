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

namespace chat\system\command;

use chat\data\message\MessageAction;
use chat\data\room\Room;
use chat\data\room\RoomList;
use chat\data\user\User as ChatUser;
use wcf\data\user\UserProfile;
use wcf\system\WCF;

/**
 * The where command shows the distribution of users among
 * the different chat rooms.
 */
class WhereCommand extends AbstractCommand implements ICommand
{
    /**
     * @inheritDoc
     */
    public function getJavaScriptModuleName()
    {
        return 'Bastelstu.be/Chat/Command/Where';
    }

    /**
     * @inheritDoc
     */
    public function validate($parameters, Room $room, ?UserProfile $user = null)
    {
        if ($user === null) {
            $user = new UserProfile(WCF::getUser());
        }
    }

    /**
     * @inheritDoc
     */
    public function execute($parameters, Room $room, ?UserProfile $user = null)
    {
        if ($user === null) {
            $user = new UserProfile(WCF::getUser());
        }

        $objectTypeID = $this->getMessageObjectTypeID('be.bastelstu.chat.messageType.where');
        $roomList = new RoomList();
        $roomList->readObjects();
        $rooms = \array_map(static function (Room $room) {
            $users = \array_map(static function (ChatUser $user) {
                return $user->jsonSerialize();
            }, $room->getUsers());

            return [
                'roomID' => $room->roomID,
                'users' => \array_values($users),
            ];
        }, \array_filter($roomList->getObjects(), static function (Room $room) {
            return $room->canSee();
        }));

        (new MessageAction(
            [ ],
            'create',
            [
                'data' => [
                    'roomID' => $room->roomID,
                    'userID' => $user->userID,
                    'username' => $user->username,
                    'time' => TIME_NOW,
                    'objectTypeID' => $objectTypeID,
                    'payload' => \serialize($rooms),
                ], 'updateTimestamp' => true,
            ]
        ))->executeAction();
    }
}
