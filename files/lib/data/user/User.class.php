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

namespace chat\data\user;

use chat\data\room\Room;
use chat\data\room\RoomCache;
use wcf\data\DatabaseObjectDecorator;
use wcf\system\WCF;

/**
 * Decorates the User object to make it useful in context of Tim’s Chat.
 */
class User extends DatabaseObjectDecorator implements \JsonSerializable
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = \wcf\data\user\User::class;

    /**
     * array of room_to_user rows
     *
     * @var int[][]
     */
    protected $roomToUser;

    /**
     * Returns an array of the room_to_user arrays for this user.
     *
     * @return  mixed[]
     */
    public function getRoomAssociations($skipCache = false)
    {
        if ($this->roomToUser === null || $skipCache) {
            $sql = "SELECT  *
                    FROM    chat1_room_to_user
                    WHERE   userID = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([ $this->userID ]);
            $this->roomToUser = [ ];
            while (($row = $statement->fetchArray())) {
                $this->roomToUser[$row['roomID']] = $row;
            }
        }

        return $this->roomToUser;
    }

    /**
     * Returns an array of Rooms this user is part of.
     *
     * @return  \chat\data\room\Room[]
     */
    public function getRooms($skipCache = false)
    {
        return \array_map(static function ($assoc) {
            return RoomCache::getInstance()->getRoom($assoc['roomID']);
        }, \array_filter($this->getRoomAssociations($skipCache), static function ($assoc) {
            return $assoc['active'] === 1;
        }));
    }

    /**
     * Returns whether the user is in the given room.
     */
    public function isInRoom(Room $room, $skipCache = false): bool
    {
        $assoc = $this->getRoomAssociations($skipCache);

        if (!isset($assoc[$room->roomID])) {
            return false;
        }

        return $assoc[$room->roomID]['active'] === 1;
    }

    /**
     * Returns (userID, roomID, sessionID) triples where the client died.
     *
     * @return  mixed[][]
     */
    public static function getDeadSessions()
    {
        $sql = "SELECT  userID,
                        roomID,
                        sessionID
                FROM    chat1_session
                WHERE   lastRequest < ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            TIME_NOW - 60 * 3,
        ]);

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        return [
            'userID' => $this->userID,
            'username' => $this->username,
            'link' => $this->getLink(),
        ];
    }
}
