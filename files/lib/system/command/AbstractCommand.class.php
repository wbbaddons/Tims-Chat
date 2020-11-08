<?php
/*
 * Copyright (c) 2010-2018 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2024-11-08
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

namespace chat\system\command;

use \chat\data\room\Room;
use \wcf\data\object\type\ObjectTypeCache;
use \wcf\data\user\UserProfile;
use \wcf\system\exception\UserInputException;

/**
 * Default implemention for command processors.
 */
abstract class AbstractCommand extends \wcf\data\DatabaseObjectDecorator implements ICommand
                                                                                  , \wcf\data\IDatabaseObjectProcessor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = \chat\data\command\Command::class;

	/**
	 * @inheritDoc
	 */
	public function isAvailable(Room $room, UserProfile $user = null) {
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function allowWithoutTrigger() {
		return false;
	}

	/**
	 * Returns the object type ID for the given message type.
	 *
	 * @param  string
	 * @return int
	 */
	public function getMessageObjectTypeID($objectType) {
		$objectTypeID = ObjectTypeCache::getInstance()->getObjectTypeIDByName('be.bastelstu.chat.messageType', $objectType);

		if (!$objectType) {
			throw new \LogicException('Missing object type');
		}

		return $objectTypeID;
	}

	/**
	 * Ensures that the given parameter exists in the parameter array and
	 * throws otherwise.
	 *
	 * @param  array  $parameters
	 * @param  string $key
	 * @return mixed  The value.
	 */
	public function assertParameter($parameters, $key) {
		if (array_key_exists($key, $parameters)) {
			return $parameters[$key];
		}

		throw new UserInputException('message');
	}
}
