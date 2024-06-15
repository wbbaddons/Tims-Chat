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

namespace chat\data\room;

use chat\system\cache\builder\RoomCacheBuilder;
use chat\system\permission\PermissionHandler;
use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;

/**
 * Represents a chat room editor.
 */
class RoomEditor extends DatabaseObjectEditor implements IEditableCachedObject
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = Room::class;

    /**
     * @inheritDoc
     */
    public static function resetCache()
    {
        RoomCacheBuilder::getInstance()->reset();
        PermissionHandler::resetCache();
    }
}
