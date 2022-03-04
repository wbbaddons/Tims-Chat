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
 * License, use of this software will be governed by version 3
 * or later of the General Public License.
 */

namespace chat\system\suspension;

use chat\data\suspension\Suspension;
use chat\system\permission\PermissionHandler;
use wcf\data\user\UserProfile;

/**
 * BanSuspension removes join privileges.
 */
final class BanSuspension implements ISuspension
{
    /**
     * @inheritDoc
     */
    public function hasEffect(Suspension $suspension): bool
    {
        $user = new UserProfile($suspension->getUser());
        $room = $suspension->getRoom();

        if ($user->getPermission('mod.chat.canBan')) {
            return false;
        }
        if ($room !== null) {
            if (
                PermissionHandler::get($user)->getPermission($room, 'mod.canBan')
                || PermissionHandler::get($user)->getPermission($room, 'mod.canIgnoreBan')
            ) {
                return false;
            }
        } else {
            if ($user->getPermission('mod.chat.canIgnoreBan')) {
                return false;
            }
        }

        return true;
    }
}
