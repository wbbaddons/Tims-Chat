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

namespace chat\system\message\type;

use \chat\data\message\Message;
use \chat\data\room\Room;
use \wcf\data\user\UserProfile;

/**
 * An IMessageType defines how a message of a certain type is acted upon.
 */
interface IMessageType {
	/**
	 * Returns the name of the JavaScript module.
	 *
	 * @return string
	 */
	public function getJavaScriptModuleName();

	/**
	 * Returns whether the given user may see the given message. If no
	 * user is given the active user should be assumed.
	 *
	 * @param	Message     $message
	 * @param	Room        $room
	 * @param	UserProfile $user
	 * @return	boolean
	 */
	public function canSee(Message $message, Room $room, UserProfile $user = null);

	/**
	 * Returns whether the given user may see the given message in the
	 * protocol. If no user is given the active user should be assumed.
	 *
	 * @param	Message     $message
	 * @param	Room        $room
	 * @param	UserProfile $user
	 * @return	boolean
	 */
	public function canSeeInLog(Message $message, Room $room, UserProfile $user = null);

	/**
	 * Returns a filtered / extended version of the message payload. If no
	 * user is given the active user should be assumed.
	 *
	 * @param	Message     $message
	 * @param	UserProfile $user
	 * @return	array
	 */
	public function getPayload(Message $message, UserProfile $user = null);

	/**
	 * Returns whether this message type supports fast select of applicable messages:
	 * If this method returns true messages with this message type will only be selected
	 * if the room ID matches. If this method returns false messages will always be selected
	 * and filtered afterwards using canSee(). Returning false is useful e.g. for broadcasts.
	 *
	 * You SHOULD return true whenever possible, for performance reasons. You MUST only return
	 * true if canSee() would return false if the given $room is not equal to the $message's room.
	 */
	public function supportsFastSelect();
}
