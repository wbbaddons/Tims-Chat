/*
 * Copyright (c) 2010-2025 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2029-04-27
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

import Helper from '../Helper'
import * as DateUtil from 'WoltLabSuite/Core/Date/Util'
import MessageType from '../MessageType'
import type Message from '../Message'

interface SuspendMessage extends Message {
	payload: {
		suspension: {
			userID: number
			username: string
			roomID: number | null
			time: number
			expires: number | null
			reason: string
			objectType: string
			judgeID: number | null // this should probably never be `null`
			judge: number
		}
		roomIDs: number[]
		globally: boolean
		target: {
			userID: number
			username: string
		}
	}
}

class Suspend extends MessageType {
	render(message: SuspendMessage): DocumentFragment | false {
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
					if (property === 'payload') {
						return payload
					}

					return target[property] as unknown
				},
			})
		)
	}

	shouldUpdateUserList(_message: Message): boolean {
		return true
	}
}

export = Suspend
