<?php
/*
 * Copyright (c) 2010-2018 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2022-11-27
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

namespace chat\system\cache\builder;

/**
 * Caches all chat rooms.
 */
class RoomCacheBuilder extends \wcf\system\cache\builder\AbstractCacheBuilder {
	/**
	 * @inheritDoc
	 */
	public function rebuild(array $parameters) {
		$roomList = new \chat\data\room\RoomList();
		$roomList->sqlOrderBy = "room.position";
		$roomList->readObjects();

		return $roomList->getObjects();
	}
}
