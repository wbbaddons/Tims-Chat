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
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

namespace chat\acp\form;

use chat\data\command\CommandList;
use chat\data\command\CommandTrigger;
use chat\data\command\CommandTriggerAction;
use wcf\form\AbstractForm;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows the command trigger add form.
 */
class CommandTriggerAddForm extends AbstractForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'chat.acp.menu.link.command.trigger.add';

    /**
     * @inheritDoc
     */
    public $neededPermissions = [
        'admin.chat.canManageTriggers',
    ];

    /**
     * The new trigger for the specified command
     * @var string
     */
    public $commandTrigger = '';

    /**
     * List of currently known commands
     * @var array
     */
    public $commands = [ ];

    /**
     * The selected command.
     *
     * @param   Command
     */
    public $command;

    /**
     * The fully qualified name of the command
     * @var string
     */
    public $className = '';

    /**
     * @inheritDoc
     */
    public function readData()
    {
        $commandList = new CommandList();
        $commandList->sqlOrderBy = 'command.className';
        $commandList->readObjects();

        $this->commands = $commandList->getObjects();

        parent::readData();
    }

    /**
     * @inheritDoc
     */
    public function readFormParameters()
    {
        parent::readFormParameters();

        if (isset($_POST['commandTrigger'])) {
            $this->commandTrigger = StringUtil::trim($_POST['commandTrigger']);
        }
        if (isset($_POST['className'])) {
            $this->className = StringUtil::trim($_POST['className']);
        }
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        parent::validate();

        if ($this->commandTrigger === '') {
            throw new UserInputException('commandTrigger', 'empty');
        }

        // Triggers must not contain whitespace
        if (\preg_match('~\s~', $this->commandTrigger)) {
            throw new UserInputException('commandTrigger', 'invalid');
        }

        // Check for duplicates
        $trigger = CommandTrigger::getTriggerByName($this->commandTrigger);
        if (
            (!isset($this->trigger) && $trigger->triggerID)
            || (isset($this->trigger) && $trigger->triggerID != $this->trigger->triggerID)
        ) {
            throw new UserInputException('commandTrigger', 'duplicate');
        }

        if ($this->className === '') {
            throw new UserInputException('className', 'empty');
        }

        // Check if the command is registered
        foreach ($this->commands as $command) {
            if ($command->className === $this->className) {
                $this->command = $command;
                break;
            }
        }

        if (!$this->command) {
            throw new UserInputException('className', 'notFound');
        }
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        parent::save();

        $fields = [
            'commandTrigger' => $this->commandTrigger,
            'commandID' => $this->command->commandID,
        ];

        // create room
        $this->objectAction = new CommandTriggerAction(
            [ ],
            'create',
            [
                'data' => \array_merge(
                    $this->additionalFields,
                    $fields
                ),
            ]
        );
        $this->objectAction->executeAction();

        $this->saved();

        // reset values
        $this->commandTrigger = $this->className = '';

        // show success message
        WCF::getTPL()->assign('success', true);
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'action' => 'add',
            'commandTrigger' => $this->commandTrigger,
            'className' => $this->className,
            'availableCommands' => $this->commands,
        ]);
    }
}
