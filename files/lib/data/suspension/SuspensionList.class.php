<?php

/*
 * Copyright (c) 2010-2022 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2026-03-04
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
