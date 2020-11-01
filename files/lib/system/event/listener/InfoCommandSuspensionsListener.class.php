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
 * License, use of this software will be governed by version 3
 * or later of the General Public License.
 */

namespace chat\system\event\listener;

use \chat\data\suspension\Suspension;
use \wcf\data\object\type\ObjectTypeCache;
use \wcf\system\WCF;

/**
 * Fetches information about the users suspensions
 */
class InfoCommandSuspensionsListener implements \wcf\system\event\listener\IParameterizedEventListener {
	/**
	 * @see	\wcf\system\event\listener\IParameterizedEventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		if (!$parameters['caller']->getPermission('admin.chat.canManageSuspensions')) {
			return;
		}

		$target = $parameters['data']['user'];

		$parameters['data']['suspensions'] = [ ];

		$suspensionList = new \chat\data\suspension\SuspensionList();
		$suspensionList->getConditionBuilder()->add('(expires IS NULL OR expires > ?)', [ TIME_NOW ]);
		$suspensionList->getConditionBuilder()->add('revoked IS NULL');
		$suspensionList->getConditionBuilder()->add('userID = ?', [ $target->userID ]);
		$suspensionList->sqlOrderBy = 'expires ASC, time ASC';
		$suspensionList->readObjects();

		$suspensions = array_filter($suspensionList->getObjects(), function (Suspension $suspension) {
			return $suspension->isActive();
		});

		$parameters['data']['suspensions'] = array_values(array_map(function ($suspension) {
			$room = \chat\data\room\RoomCache::getInstance()->getRoom($suspension->roomID);

			$suspension = $suspension->jsonSerialize();
			if ($room) {
				$suspension['room'] = [ 'title' => $room->getTitle()
				                      , 'link' => $room->getLink()
				                      ];
			}

			return $suspension;
		}, $suspensions));
	}
}
