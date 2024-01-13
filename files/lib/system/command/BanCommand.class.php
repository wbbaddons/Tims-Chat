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
 * License, use of this software will be governed by version 3
 * or later of the General Public License.
 */

namespace chat\system\command;

use chat\data\room\Room;
use chat\data\room\RoomAction;
use chat\data\suspension\Suspension;
use chat\data\user\User as ChatUser;
use chat\system\permission\PermissionHandler;
use wcf\data\user\UserProfile;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * The ban command creates a new be.bastelstu.chat.suspension.ban suspension.
 */
final class BanCommand extends AbstractSuspensionCommand implements ICommand
{
    /**
     * @inheritDoc
     */
    public function getJavaScriptModuleName()
    {
        return 'Bastelstu.be/Chat/Command/Ban';
    }

    /**
     * @inheritDoc
     */
    public function isAvailable(Room $room, ?UserProfile $user = null)
    {
        if ($user === null) {
            $user = new UserProfile(WCF::getUser());
        }

        return $user->getPermission('mod.chat.canBan') || PermissionHandler::get($user)->getPermission($room, 'mod.canBan');
    }

    /**
     * @inheritDoc
     */
    public function getObjectTypeName()
    {
        return 'be.bastelstu.chat.suspension.ban';
    }

    /**
     * @inheritDoc
     */
    protected function checkPermissions($parameters, Room $room, UserProfile $user)
    {
        $permission = $user->getPermission('mod.chat.canBan');

        if (!$this->isGlobally($parameters)) {
            $permission = $permission || PermissionHandler::get($user)->getPermission($room, 'mod.canBan');
        }

        if (!$permission) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * @inheritDoc
     */
    protected function afterCreate(Suspension $suspension, $parameters, Room $room, UserProfile $user)
    {
        parent::afterCreate($suspension, $parameters, $room, $user);

        $user = new ChatUser($suspension->getUser());
        $rooms = [ ];
        if ($suspension->getRoom() === null) {
            $rooms = $user->getRooms();
        } else {
            if ($user->isInRoom($suspension->getRoom())) {
                $rooms = [
                    $suspension->getRoom(),
                ];
            }
        }

        foreach ($rooms as $room) {
            $parameters = [
                'user' => $suspension->getUser(),
                'roomID' => $room->roomID,
            ];
            try {
                (new RoomAction(
                    [ ],
                    'leave',
                    $parameters
                ))->executeAction();
            } catch (UserInputException $e) {
                // User already left
            }
        }
    }
}
