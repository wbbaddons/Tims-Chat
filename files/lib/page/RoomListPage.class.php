<?php

/*
 * Copyright (c) 2010-2024 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2028-03-14
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

namespace chat\page;

use chat\data\room\Room;
use chat\data\room\RoomCache;
use wcf\page\AbstractPage;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;

/**
 * Shows the list of available chat rooms.
 */
final class RoomListPage extends AbstractPage
{
    /**
     * @inheritDoc
     */
    public $loginRequired = true;

    /**
     * List of rooms.
     *
     * @var \chat\data\room\Room[]
     */
    public $rooms = [ ];

    /**
     * @inheritDoc
     */
    public function checkPermissions()
    {
        parent::checkPermissions();

        if (!Room::canSeeAny()) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        $this->rooms = RoomCache::getInstance()->getRooms();
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'rooms' => $this->rooms,
        ]);
    }
}
