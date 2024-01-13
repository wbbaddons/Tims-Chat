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
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

namespace chat\data\room;

use chat\system\cache\builder\RoomCacheBuilder;
use wcf\system\SingletonFactory;

/**
 * Manages the room cache.
 */
final class RoomCache extends SingletonFactory
{
    /**
     * List of cached rooms.
     *
     * @var Room[]
     */
    protected $rooms = [ ];

    /**
     * Cached user counts for the rooms.
     *
     * @var int[]
     */
    protected $userCount = [ ];

    /**
     * @inheritDoc
     */
    protected function init()
    {
        $this->rooms = RoomCacheBuilder::getInstance()->getData();
    }

    /**
     * Returns a specific room.
     */
    public function getRoom(int $roomID): ?Room
    {
        if (isset($this->rooms[$roomID])) {
            return $this->rooms[$roomID];
        }

        return null;
    }

    /**
     * Returns all rooms.
     *
     * @return  Room[]
     */
    public function getRooms()
    {
        return $this->rooms;
    }
}
