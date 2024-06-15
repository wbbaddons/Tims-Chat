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
 * License, use of this software will be governed by version 3
 * or later of the General Public License.
 */

namespace chat\system\event\listener;

use chat\data\command\CommandCache;
use wcf\data\package\PackageCache;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\event\listener\IParameterizedEventListener;

/**
 * Adds moderator permissions to the user object.
 */
final class RoomActionGetUsersModeratorListener implements IParameterizedEventListener
{
    /**
     * @inheritDoc
     */
    public function execute($eventObj, $className, $eventName, array &$users)
    {
        $room = $eventObj->getObjects()[0]->getDecoratedObject();

        $package = PackageCache::getInstance()->getPackageByIdentifier('be.bastelstu.chat');
        $muteCommand = CommandCache::getInstance()->getCommandByPackageAndIdentifier($package, 'mute')->getProcessor();
        $banCommand = CommandCache::getInstance()->getCommandByPackageAndIdentifier($package, 'ban')->getProcessor();

        $users = \array_map(static function (array $user) use ($room, $muteCommand, $banCommand) {
            $userProfile = UserProfileRuntimeCache::getInstance()->getObject($user['userID']);
            if (!isset($user['permissions'])) {
                $user['permissions'] = [];
            }

            $user['permissions']['canMute'] = $muteCommand->isAvailable($room, $userProfile);
            $user['permissions']['canBan'] = $banCommand->isAvailable($room, $userProfile);

            return $user;
        }, $users);
    }
}
