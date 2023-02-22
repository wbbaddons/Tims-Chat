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

namespace chat\system\box;

use chat\data\room\Room;
use chat\data\room\RoomList;
use chat\page\RoomListPage;
use chat\page\RoomPage;
use wcf\system\box\AbstractDatabaseObjectListBoxController;
use wcf\system\request\LinkHandler;
use wcf\system\request\RequestHandler;
use wcf\system\WCF;

/**
 * Dynamic box controller implementation for a list of rooms.
 */
final class RoomListBoxController extends AbstractDatabaseObjectListBoxController
{
    /**
     * @inheritDoc
     */
    protected static $supportedPositions = [
        'contentBottom',
        'contentTop',
        'sidebarLeft',
        'sidebarRight',
    ];

    /**
     * @inheritDoc
     */
    protected $conditionDefinition = 'be.bastelstu.chat.box.roomList.condition';

    /**
     * @var int
     */
    protected $activeRoomID;

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct();

        $activeRequest = RequestHandler::getInstance()->getActiveRequest();
        if ($activeRequest && $activeRequest->getRequestObject() instanceof RoomPage) {
            $this->activeRoomID = $activeRequest->getRequestObject()->room->roomID;
        }
    }

    /**
     * Sets the active room ID.
     */
    public function setActiveRoomID($activeRoomID)
    {
        $this->activeRoomID = $activeRoomID;
    }

    /**
     * Returns the active room ID.
     *
     * @return  int
     */
    public function getActiveRoomID()
    {
        return $this->activeRoomID;
    }

    /**
     * @inheritDoc
     */
    public function hasLink()
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getLink(): string
    {
        return LinkHandler::getInstance()->getControllerLink(RoomListPage::class);
    }

    /**
     * @inheritDoc
     */
    protected function getObjectList()
    {
        return new RoomList();
    }

    /**
     * @inheritDoc
     */
    protected function getTemplate()
    {
        $templateName = 'boxRoomList';
        if ($this->box->position === 'sidebarLeft' || $this->box->position === 'sidebarRight') {
            $templateName = 'boxRoomListSidebar';
        }

        return WCF::getTPL()->fetch($templateName, 'chat', [
            'boxRoomList' => $this->objectList,
            'boxID' => $this->getBox()->boxID,
            'activeRoomID' => $this->activeRoomID ?: 0,
        ], true);
    }

    /**
     * @inheritDoc
     */
    public function hasContent()
    {
        if ($this->box->position === 'sidebarLeft' || $this->box->position === 'sidebarRight') {
            parent::hasContent();

            foreach ($this->objectList as $room) {
                if ($room->canSee()) {
                    return true;
                }
            }

            return false;
        } else {
            return Room::canSeeAny();
        }
    }
}
