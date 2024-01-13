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

namespace chat\data\command;

use wcf\data\DatabaseObjectList;

/**
 * Represents a list command triggers.
 *
 * @method  CommandTrigger        current()
 * @method  CommandTrigger[]      getObjects()
 * @method  CommandTrigger|null   getSingleObject()
 * @method  CommandTrigger|null   search($objectID)
 * @property    CommandTrigger[] $objects
 */
class CommandTriggerList extends DatabaseObjectList
{
}
