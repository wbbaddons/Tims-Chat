<?php
/*
 * Copyright (c) 2010-2018 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2024-11-01
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

namespace chat\data\user;

use \chat\data\room\RoomCache;
use \wcf\system\cache\runtime\UserProfileRuntimeCache;
use \wcf\system\cache\runtime\UserRuntimeCache;
use \wcf\system\exception\PermissionDeniedException;
use \wcf\system\exception\UserInputException;
use \wcf\system\WCF;

/**
 * Executes chat user-related actions.
 */
class UserAction extends \wcf\data\AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	protected $className = User::class;

	/**
	 * Validates parameters and permissions.
	 */
	public function validateGetUsersByID() {
		if (!\chat\data\room\Room::canSeeAny()) throw new PermissionDeniedException();

		$this->readIntegerArray('userIDs');
	}

	/**
	 * Returns information about the users identified by the given userIDs.
	 */
	public function getUsersByID() {
		$userList = UserProfileRuntimeCache::getInstance()->getObjects($this->parameters['userIDs']);

		return array_map(function ($user) {
			if (!$user) return null;

			$payload = [ 'image16'           => $user->getAvatar()->getImageTag(16)
			           , 'image24'           => $user->getAvatar()->getImageTag(24)
			           , 'image32'           => $user->getAvatar()->getImageTag(32)
			           , 'image48'           => $user->getAvatar()->getImageTag(48)
			           , 'imageUrl'          => $user->getAvatar()->getURL()
			           , 'link'              => $user->getLink()
			           , 'anchor'            => $user->getAnchorTag()
			           , 'userID'            => $user->userID
			           , 'username'          => $user->username
			           , 'userTitle'         => $user->getUserTitle()
			           , 'userRankClass'     => $user->getRank() ? $user->getRank()->cssClassName : null
			           , 'formattedUsername' => $user->getFormattedUsername()
			           , 'away'              => $user->chatAway
			           , 'color1'            => $user->chatColor1
			           , 'color2'            => $user->chatColor2
			           ];

			\wcf\system\event\EventHandler::getInstance()->fireAction($this, 'getUsersByID', $payload);

			return $payload;
		}, $userList);
	}

	/**
	 * Clears dead clients.
	 */
	public function clearDeadSessions() {
		$sessions = User::getDeadSessions();
		if (empty($sessions)) return;
		$userIDs = array_map(function ($item) {
			return $item['userID'];
		}, $sessions);
		$users = UserRuntimeCache::getInstance()->getObjects($userIDs);
		foreach ($sessions as $session) {
			$parameters = [ 'user' => $users[$session['userID']]
			              , 'roomID' => $session['roomID']
			              , 'sessionID' => $session['sessionID']
			              ];
			try {
				(new \chat\data\room\RoomAction([ ], 'leave', $parameters))->executeAction();
			}
			catch (UserInputException $e) {
				// Probably some other request has been faster to remove this session, ignore
			}
		}
	}

	/**
	 * @inheritDoc
	 */
	public function create() {
		throw new \BadMethodCallException();
	}

	/**
	 * @inheritDoc
	 */
	public function update() {
		throw new \BadMethodCallException();
	}

	/**
	 * @inheritDoc
	 */
	public function delete() {
		throw new \BadMethodCallException();
	}
}
