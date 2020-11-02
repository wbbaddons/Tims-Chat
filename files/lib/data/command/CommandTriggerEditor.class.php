<?php
/*
 * Copyright (c) 2010-2018 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2024-11-02
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

namespace chat\data\command;

/**
 * Represents a command trigger editor.
 */
class CommandTriggerEditor extends \wcf\data\DatabaseObjectEditor implements \wcf\data\IEditableCachedObject {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = CommandTrigger::class;

	/**
	 * @inheritDoc
	 */
	public static function resetCache() {
		\chat\system\cache\builder\CommandCacheBuilder::getInstance()->reset();
	}
}
