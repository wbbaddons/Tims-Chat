/*
 * Copyright (c) 2010-2021 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2026-03-10
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

define(['../MessageType'], function (MessageType) {
	'use strict'

	class ChatUpdate extends MessageType {
		preRender(message) {
			// TODO: hcf()?
		}

		render(message) {
			return false
		}
	}

	return ChatUpdate
})
