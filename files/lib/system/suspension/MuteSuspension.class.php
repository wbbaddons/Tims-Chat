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

namespace chat\system\suspension;

use \chat\data\suspension\Suspension;
use \chat\system\permission\PermissionHandler;
use \wcf\data\user\UserProfile;

/**
 * MuteSuspension removes write privileges.
 */
class MuteSuspension implements ISuspension {
	/**
	 * @inheritDoc
	 */
	public function hasEffect(Suspension $suspension) {
		$user = new UserProfile($suspension->getUser());
		$room = $suspension->getRoom();

		if ($user->getPermission('mod.chat.canMute')) {
			return false;
		}
		if ($room !== null) {
			if (PermissionHandler::get($user)->getPermission($room, 'mod.canMute') || PermissionHandler::get($user)->getPermission($room, 'mod.canIgnoreMute')) {
				return false;
			}
		}
		else {
			if ($user->getPermission('mod.chat.canIgnoreMute')) {
				return false;
			}
		}

		return true;
	}
}
