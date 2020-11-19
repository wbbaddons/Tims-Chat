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

namespace chat\data\room;

use \wcf\system\WCF;

/**
 * Manages the room cache.
 */
class RoomCache extends \wcf\system\SingletonFactory {
	/**
	 * List of cached rooms.
	 *
	 * @var	Room[]
	 */
	protected $rooms = [ ];

	/**
	 * Cached user counts for the rooms.
	 *
	 * @var	int[]
	 */
	protected $userCount = [ ];

	/**
	 * @inheritDoc
	 */
	protected function init() {
		$this->rooms = \chat\system\cache\builder\RoomCacheBuilder::getInstance()->getData();
	}

	/**
	 * Returns a specific room.
	 *
	 * @param	integer		$roomID
	 * @return	Room
	 */
	public function getRoom($roomID) {
		if (isset($this->rooms[$roomID])) {
			return $this->rooms[$roomID];
		}

		return null;
	}

	/**
	 * Returns all rooms.
	 *
	 * @return	Room[]
	 */
	public function getRooms() {
		return $this->rooms;
	}
}
