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

namespace chat\data\command;

use \wcf\system\WCF;

/**
 * Represents a chat command.
 */
class Command extends \wcf\data\ProcessibleDatabaseObject {
	/**
	 * @inheritDoc
	 */
	protected static $processorInterface = \chat\system\command\ICommand::class;

	/**
	 * Returns whether this command has at least one trigger assigned.
	 *
	 * The default PlainCommand implicitely has one.
	 */
	public function hasTriggers() {
		static $chatPackageID = null;

		if ($chatPackageID === null) {
			$chatPackageID = \wcf\data\package\PackageCache::getInstance()->getPackageID('be.bastelstu.chat');
		}

		if ($this->packageID === $chatPackageID && $this->identifier === 'plain') {
			return true;
		}

		$sql = "SELECT COUNT(*)
		        FROM   chat".WCF_N."_command_trigger
		        WHERE  commandID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([ $this->commandID ]);
		return $statement->fetchSingleColumn() > 0;
	}
}
