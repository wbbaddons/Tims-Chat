<?php
/*
 * Copyright (c) 2010-2018 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2024-11-20
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

namespace chat\acp\page;

/**
 * Shows the room list.
 */
class RoomListPage extends \wcf\page\SortablePage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'chat.acp.menu.link.room.list';

	/**
	 * @inheritDoc
	 */
	public $neededPermissions = [ 'admin.chat.canManageRoom' ];

	/**
	 * @inheritDoc
	 */
	public $objectListClassName = \chat\data\room\RoomList::class;

	/**
	 * @inheritDoc
	 */
	public $validSortFields = [ 'roomID', 'title' ];

	/**
	 * @inheritDoc
	 */
	public $defaultSortField = 'position';
}
