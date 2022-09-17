<?php

/*
 * Copyright (c) 2010-2022 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2026-09-17
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

namespace chat\data\command;

use wcf\data\DatabaseObjectList;

/**
 * Represents a list of chat commands.
 *
 * @method  Command        current()
 * @method  Command[]      getObjects()
 * @method  Command|null   getSingleObject()
 * @method  Command|null   search($objectID)
 * @property    Command[] $objects
 */
class CommandList extends DatabaseObjectList
{
}
