<?php

/*
 * Copyright (c) 2010-2022 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2026-03-10
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

namespace chat\data\command;

use chat\system\command\ICommand;
use wcf\data\package\PackageCache;
use wcf\data\ProcessibleDatabaseObject;
use wcf\system\WCF;

/**
 * Represents a chat command.
 */
class Command extends ProcessibleDatabaseObject
{
    /**
     * @inheritDoc
     */
    protected static $processorInterface = ICommand::class;

    /**
     * Returns whether this command has at least one trigger assigned.
     *
     * The default PlainCommand implicitely has one.
     */
    public function hasTriggers(): bool
    {
        static $chatPackageID = null;

        if ($chatPackageID === null) {
            $chatPackageID = PackageCache::getInstance()->getPackageID('be.bastelstu.chat');
        }

        if ($this->packageID === $chatPackageID && $this->identifier === 'plain') {
            return true;
        }

        $sql = "SELECT  COUNT(*)
                FROM    chat1_command_trigger
                WHERE   commandID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $this->commandID,
        ]);

        return $statement->fetchSingleColumn() > 0;
    }
}
