<?php
/*
 * Copyright (c) 2010-2018 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2024-11-20
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

namespace chat\system\cache\builder;

use \wcf\system\WCF;

/**
 * Caches all chat commands.
 */
class CommandCacheBuilder extends \wcf\system\cache\builder\AbstractCacheBuilder {
	/**
	 * @see	\wcf\system\cache\AbstractCacheBuilder::rebuild()
	 */
	public function rebuild(array $parameters) {
		$data = [ 'commands' => [ ]
		        , 'triggers' => [ ]
		        , 'packages' => [ ]
		        ];

		$commandList = new \chat\data\command\CommandList();
		$commandList->sqlOrderBy = 'command.commandID';
		$commandList->readObjects();

		$data['commands'] = $commandList->getObjects();

		foreach ($data['commands'] as $command) {
			if (!isset($data['packages'][$command->packageID])) $data['packages'][$command->packageID] = [ ];
			$data['packages'][$command->packageID][$command->identifier] = $command;
		}

		$sql = "SELECT *
		        FROM   chat".WCF_N."_command_trigger";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();

		$data['triggers'] = $statement->fetchMap('commandTrigger', 'commandID');

		return $data;
	}
}
