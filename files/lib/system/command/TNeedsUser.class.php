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
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

namespace chat\system\command;

use \wcf\data\user\User;
use \wcf\system\exception\UserInputException;
use \wcf\system\WCF;

/**
 * Adds helpful functions for commands that operate on a user.
 */
trait TNeedsUser {
	/**
	 * Returns the user with the given username.
	 *
	 * @param   string  $username
	 * @return  \wcf\data\user\User
	 */
	protected function getUser($username) {
		static $cache = [ ];
		if (!isset($cache[$username])) {
			$cache[$username] = User::getUserByUsername($username);
		}

		return $cache[$username];
	}

	/**
	 * Checks whether the given username is valid and throws otherwise.
	 *
	 * @param   string  $username
	 * @return  \wcf\data\user\User
	 */
	protected function assertUser($username) {
		$user = $this->getUser($username);

		if (!$user->userID) throw new UserInputException('message', WCF::getLanguage()->getDynamicVariable('chat.error.userNotFound', [ 'username' => $username ]));

		return $user;
	}
}
