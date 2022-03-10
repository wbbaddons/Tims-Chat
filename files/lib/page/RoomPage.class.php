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

namespace chat\page;

use chat\data\room\RoomCache;
use wcf\data\package\PackageCache;
use wcf\page\AbstractPage;
use wcf\system\attachment\AttachmentHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\NamedUserException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\push\PushHandler;
use wcf\system\WCF;

/**
 * Shows a specific chat room.
 */
final class RoomPage extends AbstractPage
{
    use TConfiguredPage;

    /**
     * Almost dummy attachment handler (used in language variable)
     *
     * @var \wcf\system\attachment\AttachmentHandler
     */
    public $attachmentHandler;

    /**
     * @inheritDoc
     */
    public $loginRequired = true;

    /**
     * The requested chat room ID.
     *
     * @var   int
     */
    public $roomID = 0;

    /**
     * The requested chat room.
     *
     * @var   \chat\data\room\Room
     */
    public $room;

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (isset($_GET['id'])) {
            $this->roomID = \intval($_GET['id']);
        }
        $this->room = RoomCache::getInstance()->getRoom($this->roomID);

        if ($this->room === null) {
            throw new IllegalLinkException();
        }
        if (!$this->room->canSee($user = null, $reason)) {
            throw $reason;
        }
        if (!$this->room->canJoin($user = null, $reason)) {
            throw $reason;
        }

        $this->canonicalURL = $this->room->getLink();
    }

    /**
     * @inheritDoc
     */
    public function checkPermissions()
    {
        parent::checkPermissions();

        $package = PackageCache::getInstance()->getPackageByIdentifier('be.bastelstu.chat');
        if (\stripos($package->packageVersion, 'Alpha') !== false) {
            $sql = "SELECT COUNT(*) FROM wcf1_user";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute();
            $userCount = $statement->fetchSingleColumn();
            if ((($userCount > 5 && !OFFLINE) || ($userCount > 30 && OFFLINE)) && \sha1(WCF_UUID) !== '643a6b3af2a6ea3d393c4d8371e75d7d1b66e0d0') {
                throw new PermissionDeniedException("Do not use alpha versions of Tims Chat in production communities!");
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        $sql = "SELECT 1";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute();
        if ($statement->fetchSingleColumn() !== 1) {
            throw new NamedUserException('PHP must be configured to use the MySQLnd driver, instead of libmysqlclient.');
        }

        parent::readData();

        // This attachment handler gets only used for the language variable `wcf.attachment.upload.limits`!
        $this->attachmentHandler = new AttachmentHandler(
            'be.bastelstu.chat.message',
            0,
            'DEADC0DE00000000DEADC0DE00000000DEADC0DE',
            $this->room->roomID
        );

        $pushHandler = PushHandler::getInstance();
        $pushHandler->joinChannel('be.bastelstu.chat');
        $pushHandler->joinChannel('be.bastelstu.chat.room-' . $this->room->roomID);
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'room' => $this->room,
            'config' => $this->getConfig(),
            'attachmentHandler' => $this->attachmentHandler,
        ]);
    }
}
