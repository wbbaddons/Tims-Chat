<?php

/*
 * Copyright (c) 2010-2024 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2028-03-14
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

namespace chat\data\command;

use chat\system\cache\builder\CommandCacheBuilder;
use wcf\data\package\Package;
use wcf\system\SingletonFactory;

/**
 * Manages the command cache.
 */
final class CommandCache extends SingletonFactory
{
    /**
     * list of cached commands
     * @var Command[]
     */
    protected $commands = [ ];

    /**
     * list of cached commands by package
     * @var Command[][]
     */
    protected $packages = [ ];

    /**
     * list of cached triggers
     * @var int[]
     */
    protected $triggers = [ ];

    /**
     * @inheritDoc
     */
    protected function init()
    {
        $data = CommandCacheBuilder::getInstance()->getData();

        $this->commands = $data['commands'];
        $this->packages = $data['packages'];
        $this->triggers = $data['triggers'];
    }

    /**
     * Returns a specific command.
     */
    public function getCommand(int $commandID): ?Command
    {
        if (isset($this->commands[$commandID])) {
            return $this->commands[$commandID];
        }

        return null;
    }

    /**
     * Returns a specific command defined by a trigger.
     */
    public function getCommandByTrigger(string $trigger): ?Command
    {
        if (isset($this->triggers[$trigger])) {
            return $this->commands[$this->triggers[$trigger]];
        }

        return null;
    }

    /**
     * Returns the command defined by the given package and identifier.
     */
    public function getCommandByPackageAndIdentifier(Package $package, string $identifier): ?Command
    {
        if (isset($this->packages[$package->packageID][$identifier])) {
            return $this->packages[$package->packageID][$identifier];
        }

        return null;
    }

    /**
     * Returns all commands.
     *
     * @return  Command[]
     */
    public function getCommands()
    {
        return $this->commands;
    }

    /**
     * Returns all triggers.
     *
     * @return int[]
     */
    public function getTriggers()
    {
        return $this->triggers;
    }
}
