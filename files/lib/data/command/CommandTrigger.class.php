<?php

/*
 * Copyright (c) 2010-2023 Tim Düsterhus.
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

namespace chat\data\command;

use wcf\data\DatabaseObject;
use wcf\system\request\IRouteController;
use wcf\system\WCF;

/**
 * Represents a chat command trigger.
 */
class CommandTrigger extends DatabaseObject implements IRouteController
{
    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        return $this->commandTrigger;
    }

    /**
     * @inheritDoc
     */
    public function getObjectID()
    {
        return $this->triggerID;
    }

    /**
     * Returns the trigger specified by its commandTrigger value
     *
     * @return CommandTrigger
     */
    public static function getTriggerByName(string $name)
    {
        $sql = "SELECT  *
                FROM    chat1_command_trigger
                WHERE   commandTrigger = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([ $name ]);
        $row = $statement->fetchArray();
        if (!$row) {
            $row = [];
        }

        return new self(null, $row);
    }
}
