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
 * License, use of this software will be governed by version 3
 * or later of the General Public License.
 */

namespace chat\data\suspension;

use \chat\data\room\Room;
use \wcf\data\user\User;
use \wcf\system\WCF;

/**
 * Represents a chat suspension.
 */
class Suspension extends \wcf\data\DatabaseObject implements \JsonSerializable {
	/**
	 * Returns the active suspensions for the given (objectTypeID, Room, User)
	 * triple.
	 *
	 * @param  int                                $objectTypeID
	 * @param  \wcf\data\user\User                $user
	 * @param  \chat\data\room\Room               $room
	 * @return \chat\data\suspension\Suspension[]
	 */
	public static function getActiveSuspensionsByTriple($objectTypeID, User $user, Room $room) {
		$suspensionList = new SuspensionList();

		$suspensionList->getConditionBuilder()->add('(expires IS NULL OR expires > ?)', [ TIME_NOW ]);
		$suspensionList->getConditionBuilder()->add('revoked IS NULL');
		$suspensionList->getConditionBuilder()->add('userID = ?', [ $user->userID ]);
		$suspensionList->getConditionBuilder()->add('objectTypeID = ?', [ $objectTypeID ]);
		$suspensionList->getConditionBuilder()->add('(roomID IS NULL OR roomID = ?)', [ $room->roomID ]);

		$suspensionList->readObjects();

		return array_filter($suspensionList->getObjects(), function (Suspension $suspension) {
			return $suspension->isActive();
		});
	}

	/**
	 * Returns the suspension object type of this message.
	 *
	 * @return	\wcf\data\object\type\ObjectType
	 */
	public function getSuspensionType() {
		return \wcf\data\object\type\ObjectTypeCache::getInstance()->getObjectType($this->objectTypeID);
	}

	/**
	 * Returns whether this suspension still is in effect.
	 *
	 * @return boolean
	 */
	public function isActive() {
		if ($this->revoked !== null) return false;
		if (!$this->getSuspensionType()->getProcessor()->hasEffect($this)) return false;

		if ($this->expires === null) return true;

		return $this->expires > TIME_NOW;
	}

	/**
	 * Returns the chat room this suspension is in effect.
	 * Returns null if this is a global suspension.
	 *
	 * @return \chat\data\room\Room
	 */
	public function getRoom() {
		if ($this->roomID === null) {
			return null;
		}

		return \chat\data\room\RoomCache::getInstance()->getRoom($this->roomID);
	}

	/**
	 * Returns the user that is affected by this suspension.
	 *
	 * @return \wcf\data\user\User
	 */
	public function getUser() {
		return \wcf\system\cache\runtime\UserRuntimeCache::getInstance()->getObject($this->userID);
	}

	/**
	 * @inheritDoc
	 */
	 public function jsonSerialize() {
		return [ 'userID'     => $this->userID
		       , 'username'   => $this->getUser()->username
		       , 'roomID'     => $this->roomID
		       , 'time'       => $this->time
		       , 'expires'    => $this->expires
		       , 'reason'     => $this->reason
		       , 'objectType' => $this->getSuspensionType()->objectType
		       , 'judgeID'    => $this->judgeID
		       , 'judge'      => $this->judge
		       ];
	}
}
