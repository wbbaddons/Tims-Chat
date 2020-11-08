<?php
/*
 * Copyright (c) 2010-2018 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2024-11-08
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

namespace chat\system\page\handler;

use \chat\data\room\Room;
use \chat\data\room\RoomCache;
use \wcf\system\WCF;

/**
 * Shows the number of chatters in the RoomList menu item.
 */
class RoomListPageHandler extends \wcf\system\page\handler\AbstractMenuPageHandler {
	/**
	 * @inheritDoc
	 */
	public function getOutstandingItemCount($objectID = null) {
		$rooms = RoomCache::getInstance()->getRooms();
		$users = array_map(function (Room $room) {
			return array_keys($room->getUsers());
		}, array_filter($rooms, function (Room $room) {
			return $room->canSee();
		}));

		if (empty($users)) return 0;

		return count(array_unique(call_user_func_array('array_merge', $users)));
	}

	/**
	 * @inheritDoc
	 */
	public function isVisible($objectID = null) {
		return Room::canSeeAny();
	}
}
