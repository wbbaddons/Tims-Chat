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

namespace chat\system\box;

use \wcf\system\request\RequestHandler;
use \wcf\system\WCF;

/**
 * Dynamic box controller implementation for a list of rooms.
 */
class RoomListBoxController extends \wcf\system\box\AbstractDatabaseObjectListBoxController {
	/**
	 * @inheritDoc
	 */
	protected static $supportedPositions = [ 'contentBottom', 'contentTop', 'sidebarLeft', 'sidebarRight' ];

	/**
	 * @inheritDoc
	 */
	protected $conditionDefinition = 'be.bastelstu.chat.box.roomList.condition';

	/**
	 * @var int
	 */
	protected $activeRoomID = null;

	/**
	 * @inheritDoc
	 */
	public function __construct() {
		parent::__construct();

		$activeRequest = RequestHandler::getInstance()->getActiveRequest();
		if ($activeRequest && $activeRequest->getRequestObject() instanceof \chat\page\RoomPage) {
			$this->activeRoomID = $activeRequest->getRequestObject()->room->roomID;
		}
	}

	/**
	 * Sets the active room ID.
	 */
	public function setActiveRoomID($activeRoomID) {
		$this->activeRoomID = $activeRoomID;
	}

	/**
	 * Returns the active room ID.
	 *
	 * @return	int
	 */
	public function getActiveRoomID() {
		return $this->activeRoomID;
	}

	/**
	 * @inheritDoc
	 */
	public function hasLink() {
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function getLink() {
		return \wcf\system\request\LinkHandler::getInstance()->getLink('RoomList', [ 'application' => 'chat' ]);
	}

	/**
	 * @inheritDoc
	 */
	protected function getObjectList() {
		return new \chat\data\room\RoomList();
	}

	/**
	 * @inheritDoc
	 */
	protected function getTemplate() {
		$templateName = 'boxRoomList';
		if ($this->box->position === 'sidebarLeft' || $this->box->position === 'sidebarRight') {
			$templateName = 'boxRoomListSidebar';
		}

		return WCF::getTPL()->fetch($templateName, 'chat', [ 'boxRoomList'  => $this->objectList
		                                                   , 'boxID'        => $this->getBox()->boxID
		                                                   , 'activeRoomID' => $this->activeRoomID ?: 0
		                                                   ], true);
	}

	/**
	 * @inheritDoc
	 */
	public function hasContent() {
		if ($this->box->position === 'sidebarLeft' || $this->box->position === 'sidebarRight') {
			parent::hasContent();

			foreach ($this->objectList as $room) {
				if ($room->canSee()) return true;
			}

			return false;
		}
		else {
			return \chat\data\room\Room::canSeeAny();
		}
	}
}
