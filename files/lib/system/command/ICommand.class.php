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

namespace chat\system\command;

use \chat\data\room\Room;
use \wcf\data\user\UserProfile;

/**
 * Interface for Command processors.
 */
interface ICommand {
	/**
	 * Returns whether the command can be used even when
	 * no trigger is configured for it.
	 *
	 * @return boolean
	 */
	public function allowWithoutTrigger();

	/**
	 * Returns the name of the JavaScript module.
	 *
	 * @return string
	 */
	public function getJavaScriptModuleName();

	/**
	 * Returns whether this command theoretically is available
	 * in the given room, for the given user.
	 * If no user is given the active user should be assumed.
	 *
	 * The return value sets a flag for the JavaScript to
	 * consume. You still need to validate() this as well!
	 *
	 * @param	Room              $room
	 * @param	UserProfile       $user
	 * @return	boolean
	 */
	public function isAvailable(Room $room, UserProfile $user = null);

	/**
	 * Validates the execution of the command with the given parameters
	 * in the given room for the given user.
	 * If no user is given the active user should be assumed.
	 * This method must throw if the command may not be executed in this form.
	 *
	 * @param	mixed             $parameters
	 * @param	Room              $room
	 * @param	UserProfile       $user
	 */
	public function validate($parameters, Room $room, UserProfile $user = null);

	/**
	 * Executes the command with the given parameters in the given room in
	 * the context of the given user.
	 * If no user is given the active user should be assumed.
	 * This method must throw if the command may not be executed in this form.
	 *
	 * @param	mixed             $parameters
	 * @param	Room              $room
	 * @param	UserProfile       $user
	 */
	public function execute($parameters, Room $room, UserProfile $user = null);
}
