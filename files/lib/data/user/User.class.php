<?php
/*
 * Copyright (c) 2010-2018 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2024-10-20
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

namespace chat\data\user;

use \wcf\system\WCF;

/**
 * Decorates the User object to make it useful in context of Tim’s Chat.
 */
class User extends \wcf\data\DatabaseObjectDecorator implements \JsonSerializable {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = \wcf\data\user\User::class;

	/**
	 * array of room_to_user rows
	 *
	 * @var	int[][]
	 */
	protected $roomToUser = null;

	/**
	 * Returns an array of the room_to_user arrays for this user.
	 *
	 * @return	mixed[]
	 */
	public function getRoomAssociations($skipCache = false) {
		if ($this->roomToUser === null || $skipCache) {
			$sql = "SELECT *
			        FROM   chat".WCF_N."_room_to_user
			        WHERE  userID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
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
	 * @return	\chat\data\room\Room[]
	 */
	public function getRooms($skipCache = false) {
		return array_map(function ($assoc) {
			return \chat\data\room\RoomCache::getInstance()->getRoom($assoc['roomID']);
		}, array_filter($this->getRoomAssociations($skipCache), function ($assoc) {
			return $assoc['active'] === 1;
		}));
	}

	/**
	 * Returns whether the user is in the given room.
	 *
	 * @param	\chat\data\room\Room $room
	 * @return	boolean
	 */
	public function isInRoom(\chat\data\room\Room $room, $skipCache = false) {
		$assoc = $this->getRoomAssociations($skipCache);

		if (!isset($assoc[$room->roomID])) return false;
		return $assoc[$room->roomID]['active'] === 1;
	}

	/**
	 * Returns (userID, roomID, sessionID) triples where the client died.
	 *
	 * @return	mixed[][]
	 */
	public static function getDeadSessions() {
		$sql = "SELECT userID, roomID, sessionID
		        FROM   chat".WCF_N."_session
		        WHERE  lastRequest < ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([ TIME_NOW - 60 * 3 ]);

		return $statement->fetchAll(\PDO::FETCH_ASSOC);
	}

	/**
	 * @inheritDoc
	 */
	public function jsonSerialize() {
		return [ 'userID'   => $this->userID
		       , 'username' => $this->username
		       , 'link'     => $this->getLink()
		       ];
	}
}
