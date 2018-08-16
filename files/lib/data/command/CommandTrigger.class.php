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

use \wcf\system\WCF;

/**
 * Represents a chat command trugger.
 */
class CommandTrigger extends \wcf\data\DatabaseObject implements \wcf\system\request\IRouteController {
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		return $this->commandTrigger;
	}

	/**
	 * @inheritDoc
	 */
	public function getObjectID() {
		return $this->triggerID;
	}

	/**
	 * Returns the trigger specified by its commandTrigger value
	 *
	 * @param  string		$name
	 * @return CommandTrigger
	 */
	public static function getTriggerByName($name) {
		$sql = "SELECT	*
			FROM	chat".WCF_N."_command_trigger
			WHERE	commandTrigger = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([ $name ]);
		$row = $statement->fetchArray();
		if (!$row) $row = [];

		return new self(null, $row);
	}
}
