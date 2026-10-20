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

interface PlainMessage extends Message {
	payload: {
		formattedMessage: string
		plaintextMessage: string
	}
}

class Plain extends MessageType {
	joinable(a: Message, b: Message): boolean {
		return a.userID === b.userID
	}

	renderPlainText(message: PlainMessage): string {
		return message.payload.plaintextMessage
	}
}

export = Plain
