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

use chat\data\room\Room;
use wcf\system\event\listener\IParameterizedEventListener;

/**
 * Hides temprooms in ACP.
 */
final class SuspensionListPageTemproomListener implements IParameterizedEventListener
{
    /**
     * @inheritDoc
     */
    public function execute($eventObj, $className, $eventName, array &$parameters)
    {
        $eventObj->availableRooms = \array_filter($eventObj->availableRooms, static function (Room $room) {
            return !$room->isTemporary;
        });
    }
}
