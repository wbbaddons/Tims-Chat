<?php
/*
 * Copyright (c) 2010-2021 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2025-02-04
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 3
 * or later of the General Public License.
 */

namespace chat\system\suspension;

use \chat\data\suspension\Suspension;
use \wcf\data\user\UserProfile;

/**
 * An ISuspension defines how a suspension of a certain type is acted upon.
 */
interface ISuspension {
	/**
	 * Returns whether the suspension actually has an effect.
	 *
	 * @param  \chat\data\suspension\Suspension $suspension
	 * @return bool
	 */
	public function hasEffect(Suspension $suspension);
}
