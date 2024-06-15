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

namespace chat\data\command;

use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes command trigger-related actions.
 */
class CommandTriggerAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
    protected $permissionsDelete = [
        'admin.chat.canManageTriggers',
    ];

    /**
     * @inheritDoc
     */
    protected $permissionsUpdate = [
        'admin.chat.canManageTriggers',
    ];
}
