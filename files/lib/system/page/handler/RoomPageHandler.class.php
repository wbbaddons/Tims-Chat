<?php

/*
 * Copyright (c) 2010-2022 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2027-02-22
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
use wcf\system\WCF;

/**
 * Allows to choose a room in the menu item management.
 */
final class RoomPageHandler extends AbstractLookupPageHandler implements IOnlineLocationPageHandler
{
    use TRoomPageHandler;
    use TOnlineLocationPageHandler;

    /**
     * @inheritDoc
     */
    public function getOutstandingItemCount($objectID = null): int
    {
        return \count(RoomCache::getInstance()->getRoom($objectID)->getUsers());
    }

    /**
     * @inheritDoc
     */
    public function getLink($objectID): string
    {
        $room = RoomCache::getInstance()->getRoom($objectID);
        if ($room === null) {
            throw new \InvalidArgumentException('Invalid room ID given');
        }

        return $room->getLink();
    }

    /**
     * @inheritDoc
     */
    public function isVisible($objectID = null): bool
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

        return $room->canSee();
    }

    /**
     * @inheritDoc
     */
    public function getOnlineLocation(Page $page, UserOnline $user): string
    {
        if ($user->pageObjectID === null) {
            return '';
        }

        $room = RoomCache::getInstance()->getRoom($user->pageObjectID);
        if ($room === null) {
            return '';
        }
        if (!$room->canSee()) {
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
