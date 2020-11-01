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

namespace chat\system\page\handler;

use \chat\data\room\RoomCache;
use \wcf\system\request\LinkHandler;
use \wcf\system\WCF;

/**
 * Allows to choose a room in the menu item management.
 */
class LogPageHandler extends \wcf\system\page\handler\AbstractLookupPageHandler implements \wcf\system\page\handler\IOnlineLocationPageHandler {
	use TRoomPageHandler;
	use \wcf\system\page\handler\TOnlineLocationPageHandler;

	/**
	 * @inheritDoc
	 */
	public function getLink($objectID) {
		$room = RoomCache::getInstance()->getRoom($objectID);
		if ($room === null) throw new \InvalidArgumentException('Invalid room ID given');

		$link = LinkHandler::getInstance()->getLink('Log', [ 'application' => 'chat'
		                                                   , 'object'      => $room
		                                                   ]);
		return $link;
	}

	/**
	 * @inheritDoc
	 */
	public function isVisible($objectID = null) {
		if (!WCF::getUser()->userID) return false;

		if ($objectID === null) throw new \InvalidArgumentException('Invalid room ID given');
		$room = RoomCache::getInstance()->getRoom($objectID);
		if ($room === null) throw new \InvalidArgumentException('Invalid room ID given');

		return $room->canSee() && $room->canSeeLog();
	}

	/**
	 * @inheritDoc
	 */
	public function getOnlineLocation(\wcf\data\page\Page $page, \wcf\data\user\online\UserOnline $user) {
		if ($user->pageObjectID === null) return '';
		$room = RoomCache::getInstance()->getRoom($user->pageObjectID);
		if ($room === null) return '';
		if (!$room->canSeeLog()) return '';

		return WCF::getLanguage()->getDynamicVariable('wcf.page.onlineLocation.'.$page->identifier, [ 'room' => $room ]);
	}
}
