<?php
/*
 * Copyright (c) 2010-2018 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2022-08-16
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

namespace chat\data\command;

/**
 * Executes command trigger-related actions.
 */
class CommandTriggerAction extends \wcf\data\AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	protected $permissionsDelete = [ 'admin.chat.canManageTriggers' ];

	/**
	 * @inheritDoc
	 */
	protected $permissionsUpdate = [ 'admin.chat.canManageTriggers' ];
}