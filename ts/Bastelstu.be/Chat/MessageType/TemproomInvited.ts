/*
 * Copyright (c) 2010-2026 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2030-10-20
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

import MessageType from '../MessageType'
import type Message from '../Message'

interface TemproomInvitedMessage extends Message {
	payload: {
		recipient: number
		recipientName: string
		room: {
			roomID: number
			title: string
			link: string
		}
	}
}

class TemproomInvited extends MessageType {}

export = TemproomInvited
