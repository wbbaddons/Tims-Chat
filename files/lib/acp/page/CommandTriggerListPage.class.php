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

namespace chat\acp\page;

/**
 * Shows the command trigger list.
 */
class CommandTriggerListPage extends \wcf\page\SortablePage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'chat.acp.menu.link.command.trigger.list';

	/**
	 * @inheritDoc
	 */
	public $neededPermissions = [ 'admin.chat.canManageTriggers' ];

	/**
	 * @inheritDoc
	 */
	public $objectListClassName = \chat\data\command\CommandTriggerList::class;

	/**
	 * @inheritDoc
	 */
	public $validSortFields = [ 'triggerID', 'commandTrigger', 'className' ];

	/**
	 * @inheritDoc
	 */
	public $defaultSortField = 'commandTrigger';

	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		parent::initObjectList();

		$this->objectList->sqlSelects = 'command.className';
		$this->objectList->sqlJoins = 'LEFT JOIN chat'.WCF_N.'_command command ON (command.commandID = command_trigger.commandID)';
	}
}
