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

namespace chat\system\condition\room;

use chat\data\room\RoomList;
use wcf\data\DatabaseObjectList;
use wcf\system\condition\AbstractCheckboxCondition;
use wcf\system\condition\IObjectListCondition;
use wcf\system\exception\ParentClassException;

/**
 * Condition implementation for rooms to only include non-empty rooms in lists.
 */
final class RoomFilledCondition extends AbstractCheckboxCondition implements IObjectListCondition
{
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
    public function addObjectListCondition(DatabaseObjectList $objectList, array $conditionData)
    {
        if (!($objectList instanceof RoomList)) {
            throw new ParentClassException(\get_class($objectList), RoomList::class);
        }

        $objectList->getConditionBuilder()->add(
            "
            EXISTS (
                SELECT  1
                FROM    chat" . WCF_N . "_room_to_user r2u
                WHERE   r2u.roomID = room.roomID
                    AND active = ?
            )",
            [ 1 ]
        );
    }
}
