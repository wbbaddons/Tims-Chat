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

namespace chat\system\cache\builder;

use chat\data\room\RoomList;
use wcf\system\cache\builder\AbstractCacheBuilder;

/**
 * Caches all chat rooms.
 */
final class RoomCacheBuilder extends AbstractCacheBuilder
{
    /**
     * @inheritDoc
     */
    public function rebuild(array $parameters)
    {
        $roomList = new RoomList();
        $roomList->sqlOrderBy = "room.position";
        $roomList->readObjects();

        return $roomList->getObjects();
    }
}
