<?php
/*
 * Copyright (c) 2010-2018 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2024-10-20
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

namespace chat\acp\form;

use \chat\data\command\CommandTrigger;
use \chat\data\command\CommandTriggerAction;
use \chat\data\command\CommandTriggerEditor;
use \wcf\system\exception\IllegalLinkException;
use \wcf\system\exception\UserInputException;
use \wcf\system\WCF;

/**
 * Shows the command trigger edit form.
 */
class CommandTriggerEditForm extends CommandTriggerAddForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'chat.acp.menu.link.command.trigger.list';

	/**
	 * The requested command trigger ID.
	 *
	 * @param	int
	 */
	public $triggerID = 0;

	/**
	 * The requested command trigger.
	 *
	 * @param	CommandTrigger
	 */
	public $trigger = null;

	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		if (isset($_REQUEST['id'])) $this->triggerID = intval($_REQUEST['id']);
		$this->trigger = new CommandTrigger($this->triggerID);

		if (!$this->trigger) {
			throw new IllegalLinkException();
		}

		parent::readParameters();
	}

	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();

		if (empty($_POST)) {
			$commandList = new \chat\data\command\CommandList();
			$commandList->getConditionBuilder()->add('command.commandID = ?', [ $this->trigger->commandID ]);
			$commandList->readObjects();
			$commands = $commandList->getObjects();

			if (!count($commands)) {
				throw new IllegalLinkException();
			}

			$this->commandTrigger = $this->trigger->commandTrigger;
			$this->className      = $commands[$this->trigger->commandID]->className;
		}
	}

	/**
	 * @inheritDoc
	 */
	public function save() {
		\wcf\form\AbstractForm::save();

		$fields = [ 'commandTrigger' => $this->commandTrigger
		          , 'commandID'      => $this->command->commandID
		          ];

		// update trigger
		$this->objectAction = new CommandTriggerAction([ $this->trigger ], 'update', [ 'data' => array_merge($this->additionalFields, $fields) ]);
		$this->objectAction->executeAction();

		$this->saved();

		// show success message
		WCF::getTPL()->assign('success', true);
	}

	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();

		WCF::getTPL()->assign([ 'action'    => 'edit'
		                      , 'triggerID' => $this->trigger->triggerID
		                      ]);
	}
}
