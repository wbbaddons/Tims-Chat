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

use chat\data\user\User as ChatUser;
use wcf\system\event\listener\IParameterizedEventListener;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;

/**
 * Denies access to temporary rooms, unless invited.
 */
final class RoomCanSeeTemproomListener implements IParameterizedEventListener
{
    /**
     * @inheritDoc
     */
    public function execute($eventObj, $className, $eventName, array &$parameters)
    {
        if (!$eventObj->isTemporary) {
            return;
        }

        $user = new ChatUser($parameters['user']->getDecoratedObject());
        if ($eventObj->ownerID === $user->userID) {
            return;
        }

        $sql = "SELECT  COUNT(*)
                FROM    chat1_room_temporary_invite
                WHERE   userID = ?
                    AND roomID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $user->userID,
            $eventObj->roomID,
        ]);
        if ($statement->fetchSingleColumn() > 0) {
            return;
        }

        $parameters['result'] = new PermissionDeniedException();
    }
}
