<?php
/*
 * Copyright (c) 2010-2018 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2024-10-20
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 3
 * or later of the General Public License.
 */

namespace chat\system\event\listener;

use \chat\system\permission\PermissionHandler;
use \wcf\system\exception\PermissionDeniedException;
use \wcf\system\WCF;

/**
 * Denies access when room is full.
 */
class RoomCanJoinUserLimitListener implements \wcf\system\event\listener\IParameterizedEventListener {
	/**
	 * @see	\wcf\system\event\listener\IParameterizedEventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		if ($eventObj->userLimit === 0) return;

		$users = $eventObj->getUsers();
		if (count($users) < $eventObj->userLimit) return;

		$user = new \chat\data\user\User($parameters['user']->getDecoratedObject());
		if ($user->isInRoom($eventObj)) return;

		$canIgnoreLimit = PermissionHandler::get($parameters['user'])->getPermission($eventObj, 'mod.canIgnoreUserLimit');
		if ($canIgnoreLimit) return;

		$parameters['result'] = new PermissionDeniedException(WCF::getLanguage()->get('chat.error.roomFull'));
	}
}
