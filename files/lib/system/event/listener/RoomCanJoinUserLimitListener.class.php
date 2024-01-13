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

namespace chat\system\event\listener;

use chat\data\user\User as ChatUser;
use chat\system\permission\PermissionHandler;
use wcf\system\event\listener\IParameterizedEventListener;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;

/**
 * Denies access when room is full.
 */
final class RoomCanJoinUserLimitListener implements IParameterizedEventListener
{
    /**
     * @inheritDoc
     */
    public function execute($eventObj, $className, $eventName, array &$parameters)
    {
        if ($eventObj->userLimit === 0) {
            return;
        }

        $users = $eventObj->getUsers();
        if (\count($users) < $eventObj->userLimit) {
            return;
        }

        $user = new ChatUser($parameters['user']->getDecoratedObject());
        if ($user->isInRoom($eventObj)) {
            return;
        }

        $canIgnoreLimit = PermissionHandler::get($parameters['user'])->getPermission($eventObj, 'mod.canIgnoreUserLimit');
        if ($canIgnoreLimit) {
            return;
        }

        $parameters['result'] = new PermissionDeniedException(
            WCF::getLanguage()->get('chat.error.roomFull')
        );
    }
}
