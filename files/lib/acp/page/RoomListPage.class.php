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

namespace chat\acp\page;

use chat\data\room\RoomList;
use wcf\page\SortablePage;

/**
 * Shows the room list.
 */
final class RoomListPage extends SortablePage
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'chat.acp.menu.link.room.list';

    /**
     * @inheritDoc
     */
    public $neededPermissions = [
        'admin.chat.canManageRoom',
    ];

    /**
     * @inheritDoc
     */
    public $objectListClassName = RoomList::class;

    /**
     * @inheritDoc
     */
    public $validSortFields = [
        'roomID',
        'title',
    ];

    /**
     * @inheritDoc
     */
    public $defaultSortField = 'position';
}
