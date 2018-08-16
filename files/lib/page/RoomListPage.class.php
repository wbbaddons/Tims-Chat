<?php
/*
 * Copyright (c) 2010-2018 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2022-08-16
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

namespace chat\page;

use \wcf\system\WCF;

/**
 * Shows the list of available chat rooms.
 */
class RoomListPage extends \wcf\page\AbstractPage {
	/**
	 * @inheritDoc
	 */
	public $loginRequired = true;

	/**
	 * List of rooms.
	 *
	 * @var	\chat\data\room\Room[]
	 */
	public $rooms = [ ];

	/**
	 * @inheritDoc
	 */
	public function checkPermissions() {
		parent::checkPermissions();

		if (!\chat\data\room\Room::canSeeAny()) throw new \wcf\system\exception\PermissionDeniedException();
	}

	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();

		$this->rooms = \chat\data\room\RoomCache::getInstance()->getRooms();
	}

	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();

		WCF::getTPL()->assign([ 'rooms' => $this->rooms ]);
	}
}
