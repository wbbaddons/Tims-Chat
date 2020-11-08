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

namespace chat\acp\page;

use \chat\data\room\Room;
use \chat\data\suspension\Suspension;
use \wcf\data\user\User;
use \wcf\system\WCF;
use \wcf\util\StringUtil;

/**
 * Shows the suspension list.
 */
class SuspensionListPage extends \wcf\page\SortablePage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'chat.acp.menu.link.suspension.list';

	/**
	 * @inheritDoc
	 */
	public $neededPermissions = [ 'admin.chat.canManageSuspensions' ];

	/**
	 * @inheritDoc
	 */
	public $objectListClassName = \chat\data\suspension\SuspensionList::class;

	/**
	 * @inheritDoc
	 */
	public $validSortFields = [ 'suspensionID', 'time', 'expires', 'revoked' ];

	/**
	 * @inheritDoc
	 */
	public $defaultSortField = 'expiresSort';

	/**
	 * @inheritDoc
	 */
	public $defaultSortOrder = 'DESC';

	/**
	 * userID filter
	 * @var int
	 */
	public $userID = null;

	/**
	 * roomID filter
	 * @var int
	 */
	public $roomID = null;

	/**
	 * objectTypeID filter
	 * @var int
	 */
	public $objectTypeID = null;

	/**
	 * judgeID filter
	 * @var int
	 */
	public $judgeID = null;

	/**
	 * Whether to show expired entries
	 * @var boolean
	 */
	public $showExpired = true;

	/**
	 * username filter
	 * @var string
	 */
	public $searchUsername = null;

	/**
	 * judge's username filter
	 * @var string
	 */
	public $searchJudge = null;

	/**
	 * Array of available suspension object types
	 * @var array
	 */
	public $availableObjectTypes = [ ];

	/**
	 * Array of available chat rooms
	 * @var array
	 */
	public $availableRooms = [ ];

	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();

		if (isset($_REQUEST['roomID']) && $_REQUEST['roomID'] !== '') $this->roomID = intval($_REQUEST['roomID']);
		if (isset($_REQUEST['userID']) && $_REQUEST['userID'] !== '') $this->userID = intval($_REQUEST['userID']);
		if (isset($_REQUEST['judgeID']) && $_REQUEST['judgeID'] !== '') $this->judgeID = intval($_REQUEST['judgeID']);
		if (isset($_REQUEST['objectTypeID']) && $_REQUEST['objectTypeID'] !== '') $this->objectTypeID = intval($_REQUEST['objectTypeID']);
		// Checkboxes need special handling
		if (!empty($_POST) && !isset($_POST['showExpired'])) $this->showExpired = false;

		if (isset($_POST['searchUsername'])) {
			$this->searchUsername = StringUtil::trim($_POST['searchUsername']);

			if (!empty($this->searchUsername)) {
				$this->userID = User::getUserByUsername($this->searchUsername)->userID;
			}
		}
		else if ($this->userID !== null) {
			$this->searchUsername = (new User($this->userID))->username;
		}

		if (isset($_POST['searchJudge'])) {
			$this->searchJudge = StringUtil::trim($_POST['searchJudge']);

			if (!empty($this->searchJudge)) {
				$this->judgeID = User::getUserByUsername($this->searchJudge)->userID;
			}
		}
		else if ($this->judgeID !== null) {
			$this->searchJudge = (new User($this->judgeID))->username;
		}
	}

	/**
	 * @inheritDoc
	 */
	public function readData() {
		$this->availableObjectTypes = \wcf\data\object\type\ObjectTypeCache::getInstance()->getObjectTypes('be.bastelstu.chat.suspension');

		$roomList = new \chat\data\room\RoomList();
		$roomList->sqlOrderBy = "room.position";
		$roomList->readObjects();
		$this->availableRooms = $roomList->getObjects();

		parent::readData();

		\wcf\system\cache\runtime\UserRuntimeCache::getInstance()->cacheObjectIDs(array_map(function (Suspension $s) {
			return $s->userID;
		}, $this->objectList->getObjects()));
	}

	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		parent::initObjectList();

		$this->objectList->sqlSelects .= 'COALESCE(suspension.revoked, suspension.expires, 2147483647) AS expiresSort';

		if (!empty($this->availableRooms)) {
			$this->objectList->getConditionBuilder()->add('(roomID IN (?) OR roomID IS NULL)', [ array_map(function (Room $room) {
				return $room->roomID;
			}, $this->availableRooms) ]);
		}
		else {
			$this->objectList->getConditionBuilder()->add('1 = 0');
		}

		if ($this->userID !== null) {
			$this->objectList->getConditionBuilder()->add('userID = ?', [ $this->userID ]);
		}

		if ($this->roomID !== null) {
			if ($this->roomID === 0) {
				$this->objectList->getConditionBuilder()->add('roomID IS NULL');
			}
			else {
				$this->objectList->getConditionBuilder()->add('roomID = ?', [ $this->roomID ]);
			}
		}

		if ($this->objectTypeID !== null) {
			$this->objectList->getConditionBuilder()->add('objectTypeID = ?', [ $this->objectTypeID ]);
		}

		if ($this->judgeID !== null) {
			$this->objectList->getConditionBuilder()->add('judgeID = ?', [ $this->judgeID ]);
		}

		if ($this->showExpired === false) {
			$this->objectList->getConditionBuilder()->add('expires >= ?', [ TIME_NOW ]);
		}
	}

	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();

		WCF::getTPL()->assign([ 'userID'               => $this->userID
		                      , 'roomID'               => $this->roomID
		                      , 'objectTypeID'         => $this->objectTypeID
		                      , 'judgeID'              => $this->judgeID
		                      , 'availableRooms'       => $this->availableRooms
		                      , 'availableObjectTypes' => $this->availableObjectTypes
		                      , 'searchUsername'       => $this->searchUsername
		                      , 'searchJudge'          => $this->searchJudge
		                      , 'showExpired'          => $this->showExpired
		                      ]);
	}
}
