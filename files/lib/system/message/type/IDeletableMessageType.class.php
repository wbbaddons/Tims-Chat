<?php
/*
 * Copyright (c) 2010-2018 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2024-11-01
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

namespace chat\system\message\type;

use \chat\data\message\Message;
use \wcf\data\user\UserProfile;

/**
 * An IDeletableMessageType defines that the implementing message type supports message deletion.
 */
interface IDeletableMessageType extends IMessageType {
	/**
	 * Returns whether the given user may delete the given message. If no
	 * user is given the active user should be assumed.
	 *
	 * @param	Message     $message
	 * @param	UserProfile $user
	 * @return	boolean
	 */
	public function canDelete(Message $message, UserProfile $user = null);
}
