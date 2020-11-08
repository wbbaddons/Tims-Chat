/*
 * Copyright (c) 2010-2018 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2024-11-08
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

define(['../MessageType'], function (MessageType) {
	'use strict'

	class Unsuspend extends MessageType {
		shouldUpdateUserList(message) {
			return true
		}
	}

	return Unsuspend
})
