<?php

/*
 * Copyright (c) 2010-2022 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2027-02-22
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

namespace chat\system\cache\builder;

use chat\data\command\CommandList;
use wcf\system\cache\builder\AbstractCacheBuilder;
use wcf\system\WCF;

/**
 * Caches all chat commands.
 */
final class CommandCacheBuilder extends AbstractCacheBuilder
{
    /**
     * @inheritDoc
     */
    public function rebuild(array $parameters)
    {
        $data = [
            'commands' => [ ],
            'triggers' => [ ],
            'packages' => [ ],
        ];

        $commandList = new CommandList();
        $commandList->sqlOrderBy = 'command.commandID';
        $commandList->readObjects();

        $data['commands'] = $commandList->getObjects();

        foreach ($data['commands'] as $command) {
            if (!isset($data['packages'][$command->packageID])) {
                $data['packages'][$command->packageID] = [ ];
            }
            $data['packages'][$command->packageID][$command->identifier] = $command;
        }

        $sql = "SELECT  *
                FROM    chat1_command_trigger";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute();

        $data['triggers'] = $statement->fetchMap('commandTrigger', 'commandID');

        return $data;
    }
}
