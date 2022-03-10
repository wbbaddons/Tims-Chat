<?php

/*
 * Copyright (c) 2010-2022 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2026-03-10
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

namespace chat\data\room;

use wcf\data\DatabaseObjectList;

/**
 * Represents a list of chat rooms.
 *
 * @method  Room        current()
 * @method  Room[]      getObjects()
 * @method  Room|null   getSingleObject()
 * @method  Room|null   search($objectID)
 * @property    Room[] $objects
 */
class RoomList extends DatabaseObjectList
{
    /**
     * @inheritDoc
     */
    public $sqlOrderBy = 'position';
}
