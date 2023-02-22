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

namespace chat\system\page\handler;

use chat\data\room\Room;
use chat\data\room\RoomCache;
use wcf\system\page\handler\AbstractMenuPageHandler;

/**
 * Shows the number of chatters in the RoomList menu item.
 */
final class RoomListPageHandler extends AbstractMenuPageHandler
{
    /**
     * @inheritDoc
     */
    public function getOutstandingItemCount($objectID = null): int
    {
        $rooms = RoomCache::getInstance()->getRooms();
        $users = \array_map(static function (Room $room) {
            return \array_keys($room->getUsers());
        }, \array_filter($rooms, static function (Room $room) {
            return $room->canSee();
        }));

        if ($users === []) {
            return 0;
        }

        return \count(\array_unique(\call_user_func_array('array_merge', $users)));
    }

    /**
     * @inheritDoc
     */
    public function isVisible($objectID = null): bool
    {
        return Room::canSeeAny();
    }
}
