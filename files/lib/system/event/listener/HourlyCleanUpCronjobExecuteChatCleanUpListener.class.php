<?php
/*
 * Copyright (c) 2010-2021 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2026-03-04
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

namespace chat\system\event\listener;

use \wcf\system\WCF;

/**
 * Vaporizes unneeded data.
 */
class HourlyCleanUpCronjobExecuteChatCleanUpListener implements \wcf\system\event\listener\IParameterizedEventListener {
	/**
	 * @see	\wcf\system\event\listener\IParameterizedEventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		(new \chat\data\message\MessageAction([ ], 'prune'))->executeAction();
		(new \chat\data\user\UserAction([], 'clearDeadSessions'))->executeAction();

		$sql = "UPDATE chat1_room_to_user
		        SET    active = ?
		        WHERE      (roomID, userID) NOT IN (SELECT roomID, userID FROM chat1_session)
		               AND active = ?";
		$statement = WCF::getDB()->prepare($sql);
		$statement->execute([ 0, 1 ]);
		if ($statement->getAffectedRows()) {
			\wcf\functions\exception\logThrowable(new \Exception('Unreachable'));
		}
	}
}
