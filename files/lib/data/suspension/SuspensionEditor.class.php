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
 * License, use of this software will be governed by version 3
 * or later of the General Public License.
 */

namespace chat\data\suspension;

/**
 * Represents a chat suspension editor.
 */
class SuspensionEditor extends \wcf\data\DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = Suspension::class;
}
