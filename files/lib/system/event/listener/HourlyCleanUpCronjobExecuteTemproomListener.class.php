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
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

namespace chat\system\event\listener;

use chat\data\room\RoomAction;
use chat\data\room\RoomList;
use wcf\system\event\listener\IParameterizedEventListener;
use wcf\system\WCF;

/**
 * Removes empty temporary rooms.
 */
final class HourlyCleanUpCronjobExecuteTemproomListener implements IParameterizedEventListener
{
    /**
     * @inheritDoc
     */
    public function execute($eventObj, $className, $eventName, array &$parameters)
    {
        $roomList = new RoomList();
        $roomList->getConditionBuilder()->add('isTemporary = ?', [ 1 ]);
        $roomList->readObjects();

        $toDelete = [ ];
        WCF::getDB()->beginTransaction();
        foreach ($roomList as $room) {
            if (\count($room->getUsers()) === 0) {
                $toDelete[] = $room;
            }
        }
        if ($toDelete !== []) {
            (new RoomAction(
                $toDelete,
                'delete'
            ))->executeAction();
        }
        WCF::getDB()->commitTransaction();
    }
}
