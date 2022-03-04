<?php

/*
 * Copyright (c) 2010-2022 Tim Düsterhus.
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

namespace chat\system\page\handler;

use chat\data\room\RoomCache;
use wcf\data\page\Page;
use wcf\data\user\online\UserOnline;
use wcf\system\page\handler\AbstractLookupPageHandler;
use wcf\system\page\handler\IOnlineLocationPageHandler;
use wcf\system\page\handler\TOnlineLocationPageHandler;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Allows to choose a room in the menu item management.
 */
class LogPageHandler extends AbstractLookupPageHandler implements IOnlineLocationPageHandler
{
    use TRoomPageHandler;
    use TOnlineLocationPageHandler;

    /**
     * @inheritDoc
     */
    public function getLink($objectID)
    {
        $room = RoomCache::getInstance()->getRoom($objectID);
        if ($room === null) {
            throw new \InvalidArgumentException('Invalid room ID given');
        }

        return LinkHandler::getInstance()->getLink(
            'Log',
            [
                'application' => 'chat',
                'object' => $room,
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function isVisible($objectID = null)
    {
        if (!WCF::getUser()->userID) {
            return false;
        }

        if ($objectID === null) {
            throw new \InvalidArgumentException('Invalid room ID given');
        }
        $room = RoomCache::getInstance()->getRoom($objectID);
        if ($room === null) {
            throw new \InvalidArgumentException('Invalid room ID given');
        }

        return $room->canSee() && $room->canSeeLog();
    }

    /**
     * @inheritDoc
     */
    public function getOnlineLocation(Page $page, UserOnline $user)
    {
        if ($user->pageObjectID === null) {
            return '';
        }

        $room = RoomCache::getInstance()->getRoom($user->pageObjectID);

        if ($room === null) {
            return '';
        }
        if (!$room->canSeeLog()) {
            return '';
        }

        return WCF::getLanguage()->getDynamicVariable(
            'wcf.page.onlineLocation.' . $page->identifier,
            [
                'room' => $room,
            ]
        );
    }
}
