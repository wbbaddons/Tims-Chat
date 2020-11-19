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

namespace chat\system\event\listener;

use \wcf\system\WCF;

/**
 * Removes empty temporary rooms.
 */
class HourlyCleanUpCronjobExecuteTemproomListener implements \wcf\system\event\listener\IParameterizedEventListener {
	/**
	 * @see	\wcf\system\event\listener\IParameterizedEventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		$roomList = new \chat\data\room\RoomList();
		$roomList->getConditionBuilder()->add('isTemporary = ?', [ 1 ]);
		$roomList->readObjects();

		$toDelete = [ ];
		WCF::getDB()->beginTransaction();
		foreach ($roomList as $room) {
			if (count($room->getUsers()) === 0) {
				$toDelete[] = $room;
			}
		}
		if (!empty($toDelete)) {
			(new \chat\data\room\RoomAction($toDelete, 'delete'))->executeAction();
		}
		WCF::getDB()->commitTransaction();
	}
}
