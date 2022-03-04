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

namespace chat\data\room;

use chat\system\cache\runtime\UserRuntimeCache as ChatUserRuntimeCache;
use chat\system\permission\PermissionHandler;
use wcf\data\DatabaseObject;
use wcf\data\ITitledLinkObject;
use wcf\data\user\UserProfile;
use wcf\system\event\EventHandler;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\request\IRouteController;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Represents a chat room.
 *
 * @property-read   integer $roomID
 * @property-read   string  $title
 * @property-read   string  $topic
 * @property-read   integer $position
 * @property-read   integer $userLimit
 * @property-read   integer $isTemporary
 * @property-read   integer $ownerID
 * @property-read   integer $topicUseHtml
 */
final class Room extends DatabaseObject implements
    IRouteController,
    ITitledLinkObject,
    \JsonSerializable
{
    /**
     * @var ?(integer[])
     */
    private static $userToRoom;

    /**
     * @see Room::getTitle()
     */
    public function __toString(): string
    {
        return $this->getTitle();
    }

    /**
     * Returns whether the given user can see at least
     * one chat room. If no user is given the current user
     * should be assumed
     */
    public static function canSeeAny(?UserProfile $user = null): bool
    {
        $rooms = RoomCache::getInstance()->getRooms();
        foreach ($rooms as $room) {
            if ($room->canSee($user)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns whether the given user can see this room.
     * If no user is given the current user should be assumed.
     */
    public function canSee(?UserProfile $user = null, ?\Exception &$reason = null): bool
    {
        static $cache = [ ];
        if ($user === null) {
            $user = new UserProfile(WCF::getUser());
        }

        if (!isset($cache[$this->roomID])) {
            $cache[$this->roomID] = [];
        }
        if (\array_key_exists($user->userID, $cache[$this->roomID])) {
            return ($reason = $cache[$this->roomID][$user->userID]) === null;
        }

        if (!$user->userID) {
            $reason = new PermissionDeniedException();

            return ($cache[$this->roomID][$user->userID] = $reason) === null;
        }

        $result = null;
        if (!PermissionHandler::get($user)->getPermission($this, 'user.canSee')) {
            $result = new PermissionDeniedException();
        }

        $parameters = [
            'user' => $user,
            'result' => $result,
        ];
        EventHandler::getInstance()->fireAction($this, 'canSee', $parameters);
        $reason = $parameters['result'];

        if (!($reason === null || $reason instanceof \Exception || $reason instanceof \Throwable)) {
            throw new \DomainException('Result of canSee must be a \Throwable or null.');
        }

        return ($cache[$this->roomID][$user->userID] = $reason) === null;
    }

    /**
     * Returns whether the given user can see the log of this room.
     * If no user is given the current user should be assumed.
     */
    public function canSeeLog(?UserProfile $user = null, ?\Exception &$reason = null): bool
    {
        static $cache = [ ];
        if ($user === null) {
            $user = new UserProfile(WCF::getUser());
        }

        if (!isset($cache[$this->roomID])) {
            $cache[$this->roomID] = [];
        }
        if (\array_key_exists($user->userID, $cache[$this->roomID])) {
            return ($reason = $cache[$this->roomID][$user->userID]) === null;
        }

        $result = null;
        if (!PermissionHandler::get($user)->getPermission($this, 'user.canSeeLog')) {
            $result = new PermissionDeniedException();
        }

        $parameters = [
            'user' => $user,
            'result' => $result,
        ];
        EventHandler::getInstance()->fireAction($this, 'canSeeLog', $parameters);
        $reason = $parameters['result'];

        if (!($reason === null || $reason instanceof \Exception || $reason instanceof \Throwable)) {
            throw new \DomainException('Result of canSeeLog must be a \Throwable or null.');
        }

        return ($cache[$this->roomID][$user->userID] = $reason) === null;
    }

    /**
     * Returns whether the given user can join this room.
     * If no user is given the current user should be assumed.
     */
    public function canJoin(?UserProfile $user = null, ?\Exception &$reason = null): bool
    {
        static $cache = [ ];
        if ($user === null) {
            $user = new UserProfile(WCF::getUser());
        }

        if (!isset($cache[$this->roomID])) {
            $cache[$this->roomID] = [];
        }
        if (\array_key_exists($user->userID, $cache[$this->roomID])) {
            return ($reason = $cache[$this->roomID][$user->userID]) === null;
        }

        $parameters = [
            'user' => $user,
            'result' => null,
        ];
        EventHandler::getInstance()->fireAction($this, 'canJoin', $parameters);
        $reason = $parameters['result'];

        if (!($reason === null || $reason instanceof \Exception || $reason instanceof \Throwable)) {
            throw new \DomainException('Result of canJoin must be a \Throwable or null.');
        }

        return ($cache[$this->roomID][$user->userID] = $reason) === null;
    }

    /**
     * Returns whether the given user can write public messages in this room.
     * If no user is given the current user should be assumed.
     */
    public function canWritePublicly(?UserProfile $user = null, ?\Exception &$reason = null): bool
    {
        static $cache = [ ];
        if ($user === null) {
            $user = new UserProfile(WCF::getUser());
        }

        if (!isset($cache[$this->roomID])) {
            $cache[$this->roomID] = [];
        }
        if (\array_key_exists($user->userID, $cache[$this->roomID])) {
            return ($reason = $cache[$this->roomID][$user->userID]) === null;
        }

        $result = null;
        if (!PermissionHandler::get($user)->getPermission($this, 'user.canWrite')) {
            $result = new PermissionDeniedException();
        }

        $parameters = [
            'user' => $user,
            'result' => $result,
        ];
        EventHandler::getInstance()->fireAction($this, 'canWritePublicly', $parameters);
        $reason = $parameters['result'];

        if (!($reason === null || $reason instanceof \Exception || $reason instanceof \Throwable)) {
            throw new \DomainException('Result of canWritePublicly must be a \Throwable or null.');
        }

        return ($cache[$this->roomID][$user->userID] = $reason) === null;
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        return WCF::getLanguage()->get($this->title);
    }

    /**
     * @inheritDoc
     */
    public function getTopic(): string
    {
        $topic = StringUtil::trim(WCF::getLanguage()->get($this->topic));

        if (!$this->topicUseHtml) {
            $topic = StringUtil::encodeHTML($topic);
        }

        return $topic;
    }

    /**
     * Returns an array of users in this room.
     *
     * @return \chat\data\user\User[]
     */
    public function getUsers()
    {
        if (self::$userToRoom === null) {
            $sql = "SELECT      r2u.userID,
                                r2u.roomID
                    FROM        chat1_room_to_user r2u
                    INNER JOIN  wcf1_user u
                            ON  r2u.userID = u.userID
                    WHERE       r2u.active = ?
                    ORDER BY    u.username ASC";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([ 1 ]);
            self::$userToRoom = $statement->fetchMap('roomID', 'userID', false);

            if (!empty(self::$userToRoom)) {
                ChatUserRuntimeCache::getInstance()->cacheObjectIDs(\array_merge(...self::$userToRoom));
            }
        }

        if (!isset(self::$userToRoom[$this->roomID])) {
            return [ ];
        }

        return ChatUserRuntimeCache::getInstance()->getObjects(self::$userToRoom[$this->roomID]);
    }

    /**
     * @inheritDoc
     */
    public function getLink(): string
    {
        return LinkHandler::getInstance()->getLink(
            'Room',
            [
                'application' => 'chat',
                'object' => $this,
                'forceFrontend' => true,
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return [
            'title' => $this->getTitle(),
            'topic' => $this->getTopic(),
            'link' => $this->getLink(),
        ];
    }
}
