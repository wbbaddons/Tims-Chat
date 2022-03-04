<?php

/*
 * Copyright (c) 2010-2022 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2026-03-04
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 3
 * or later of the General Public License.
 */

namespace chat\system\event\listener;

use wcf\system\event\listener\IParameterizedEventListener;
use wcf\system\exception\PermissionDeniedException;

/**
 * Disallow editing of temprooms in ACP.
 */
class RoomEditFormTemproomListener implements IParameterizedEventListener
{
    /**
     * @inheritDoc
     */
    public function execute($eventObj, $className, $eventName, array &$parameters)
    {
        if ($eventObj->room->isTemporary) {
            throw new PermissionDeniedException();
        }
    }
}
