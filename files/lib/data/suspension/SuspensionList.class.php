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

namespace chat\data\suspension;

use wcf\data\DatabaseObjectList;

/**
 * Represents a list of chat suspensions.
 *
 * @method  Suspension        current()
 * @method  Suspension[]      getObjects()
 * @method  Suspension|null   getSingleObject()
 * @method  Suspension|null   search($objectID)
 * @property    Suspension[] $objects
 */
class SuspensionList extends DatabaseObjectList
{
}
