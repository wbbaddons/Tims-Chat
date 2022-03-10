<?php

/*
 * Copyright (c) 2010-2022 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2026-03-10
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

namespace chat\acp\form;

use chat\data\room\Room;
use chat\data\room\RoomAction;
use wcf\data\package\PackageCache;
use wcf\form\AbstractForm;
use wcf\system\acl\ACLHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\language\I18nHandler;
use wcf\system\WCF;

/**
 * Shows the room edit form.
 */
class RoomEditForm extends RoomAddForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'chat.acp.menu.link.room.list';

    /**
     * The requested chat room ID.
     *
     * @param   int
     */
    public $roomID = 0;

    /**
     * The requested chat room.
     *
     * @param   Room
     */
    public $room;

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        if (isset($_REQUEST['id'])) {
            $this->roomID = \intval($_REQUEST['id']);
        }
        $this->room = new Room($this->roomID);

        if (!$this->room) {
            throw new IllegalLinkException();
        }

        parent::readParameters();
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        if (empty($_POST)) {
            $packageID = PackageCache::getInstance()->getPackageID('be.bastelstu.chat');
            I18nHandler::getInstance()->setOptions(
                'title',
                $packageID,
                $this->room->title,
                'chat.room.room\d+.title'
            );
            I18nHandler::getInstance()->setOptions(
                'topic',
                $packageID,
                $this->room->topic,
                'chat.room.room\d+.topic'
            );
            $this->userLimit = $this->room->userLimit;
            $this->topicUseHtml = $this->room->topicUseHtml;
        }
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        AbstractForm::save();

        $fields = [
            'title' => $this->title,
            'topic' => $this->topic,
            'topicUseHtml' => (int)$this->topicUseHtml,
            'userLimit' => $this->userLimit,
            'position' => 0, // TODO
        ];

        // update room
        $this->objectAction = new RoomAction(
            [ $this->room ],
            'update',
            [
                'data' => \array_merge(
                    $this->additionalFields,
                    $fields
                ),
            ]
        );
        $returnValues = $this->objectAction->executeAction();

        // save i18n values
        $this->saveI18nValue(
            $this->room,
            [
                'title',
                'topic',
            ]
        );

        // save ACL
        ACLHandler::getInstance()->save(
            $this->room->roomID,
            $this->aclObjectTypeID
        );

        $this->saved();

        // show success message
        WCF::getTPL()->assign('success', true);
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        I18nHandler::getInstance()->assignVariables(!empty($_POST));

        WCF::getTPL()->assign([
            'action' => 'edit',
            'roomID' => $this->room->roomID,
            'room' => $this->room,
        ]);
    }
}
