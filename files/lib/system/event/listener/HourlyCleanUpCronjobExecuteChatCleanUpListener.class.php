<?php

/*
 * Copyright (c) 2010-2022 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2027-02-22
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

namespace chat\system\event\listener;

use chat\data\message\MessageAction;
use chat\data\user\UserAction as ChatUserAction;
use wcf\system\event\listener\IParameterizedEventListener;
use wcf\system\WCF;

/**
 * Vaporizes unneeded data.
 */
final class HourlyCleanUpCronjobExecuteChatCleanUpListener implements IParameterizedEventListener
{
    /**
     * @inheritDoc
     */
    public function execute($eventObj, $className, $eventName, array &$parameters)
    {
        (new MessageAction([ ], 'prune'))->executeAction();
        (new ChatUserAction([ ], 'clearDeadSessions'))->executeAction();

        $sql = "UPDATE  chat1_room_to_user
                SET     active = ?
                WHERE   (roomID, userID) NOT IN (
                            SELECT  roomID, userID
                            FROM    chat1_session
                        )
                    AND active = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([ 0, 1 ]);
        if ($statement->getAffectedRows()) {
            \wcf\functions\exception\logThrowable(new \Exception('Unreachable'));
        }
    }
}
