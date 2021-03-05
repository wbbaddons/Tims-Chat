<?php
/*
 * Copyright (c) 2010-2021 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2025-03-05
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

namespace chat\system\condition\room;

use \chat\data\room\RoomList;
use \wcf\data\DatabaseObject;
use \wcf\data\DatabaseObjectList;
use \wcf\system\exception\SystemException;

/**
 * Condition implementation for rooms to only include non-empty rooms in lists.
 */
class RoomFilledCondition extends \wcf\system\condition\AbstractCheckboxCondition implements \wcf\system\condition\IObjectListCondition {
	/**
	 * @inheritDoc
	 */
	protected $fieldName = 'chatRoomIsFilled';

	/**
	 * @inheritDoc
	 */
	protected $label = 'chat.room.condition.isFilled';

	/**
	 * @inheritDoc
	 */
	public function addObjectListCondition(DatabaseObjectList $objectList, array $conditionData) {
		if (!($objectList instanceof RoomList)) {
			throw new \wcf\system\exception\ParentClassException(get_class($objectList), RoomList::class);
		}

		$objectList->getConditionBuilder()->add("EXISTS (SELECT 1 FROM chat".WCF_N."_room_to_user r2u WHERE r2u.roomID = room.roomID AND active = ?)", [ 1 ]);
	}
}
