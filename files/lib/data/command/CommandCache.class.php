<?php
/*
 * Copyright (c) 2010-2018 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2022-11-27
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

namespace chat\data\command;

/**
 * Manages the command cache.
 */
class CommandCache extends \wcf\system\SingletonFactory {
	/**
	 * list of cached commands
	 * @var	Command[]
	 */
	protected $commands = [ ];

	/**
	 * list of cached commands by package
	 * @var Command[][]
	 */
	protected $packages = [ ];

	/**
	 * list of cached triggers
	 * @var	int[]
	 */
	protected $triggers = [ ];

	/**
	 * @inheritDoc
	 */
	protected function init() {
		$data = \chat\system\cache\builder\CommandCacheBuilder::getInstance()->getData();

		$this->commands = $data['commands'];
		$this->packages = $data['packages'];
		$this->triggers = $data['triggers'];
	}

	/**
	 * Returns a specific command.
	 *
	 * @param	integer		$commandID
	 * @return	Command
	 */
	public function getCommand($commandID) {
		if (isset($this->commands[$commandID])) {
			return $this->commands[$commandID];
		}

		return null;
	}

	/**
	 * Returns a specific command defined by a trigger.
	 *
	 * @param	string		$trigger
	 * @return	Command
	 */
	public function getCommandByTrigger($trigger) {
		if (isset($this->triggers[$trigger])) {
			return $this->commands[$this->triggers[$trigger]];
		}

		return null;
	}

	/**
	 * Returns the command defined by the given package and identifier.
	 *
	 * @param	\wcf\data\package\Package  $package
	 * @param	string                     $identifier
	 * @return	Command
	 */
	public function getCommandByPackageAndIdentifier(\wcf\data\package\Package $package, $identifier) {
		if (isset($this->packages[$package->packageID][$identifier])) {
			return $this->packages[$package->packageID][$identifier];
		}

		return null;
	}

	/**
	 * Returns all commands.
	 *
	 * @return	Command[]
	 */
	public function getCommands() {
		return $this->commands;
	}

	/**
	 * Returns all triggers.
	 *
	 * @return int[]
	 */
	public function getTriggers() {
		return $this->triggers;
	}
}
