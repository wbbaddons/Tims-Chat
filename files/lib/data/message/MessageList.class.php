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

namespace chat\data\message;

use wcf\data\DatabaseObjectList;

/**
 * Represents a list of chat messages.
 *
 * @method  Message        current()
 * @method  Message[]      getObjects()
 * @method  Message|null   getSingleObject()
 * @method  Message|null   search($objectID)
 * @property    Message[] $objects
 */
class MessageList extends DatabaseObjectList
{
}
