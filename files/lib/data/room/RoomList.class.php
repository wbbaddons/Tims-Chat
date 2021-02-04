<?php
/*
 * Copyright (c) 2010-2021 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2025-02-04
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

namespace chat\data\room;

/**
 * Represents a list of chat rooms.
 */
class RoomList extends \wcf\data\DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $sqlOrderBy = 'position';
}
