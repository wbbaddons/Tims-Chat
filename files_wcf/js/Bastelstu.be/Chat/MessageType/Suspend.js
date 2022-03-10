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

define([
	'../Helper',
	'WoltLabSuite/Core/Date/Util',
	'../MessageType',
], function (Helper, DateUtil, MessageType) {
	'use strict'

	class Suspend extends MessageType {
		render(message) {
			const expires =
				message.payload.suspension.expires !== null
					? new Date(message.payload.suspension.expires * 1000)
					: null
			const formattedExpires =
				expires !== null ? DateUtil.formatDateTime(expires) : null
			const aug = { expires, formattedExpires }
			const suspension = Object.assign({}, message.payload.suspension, aug)
			const payload = Helper.deepFreeze(
				Object.assign({}, message.payload, { suspension })
			)

			return super.render(
				new Proxy(message, {
					get: function (target, property) {
						if (property === 'payload') return payload
						return target[property]
					},
				})
			)
		}

		shouldUpdateUserList(message) {
			return true
		}
	}

	return Suspend
})
