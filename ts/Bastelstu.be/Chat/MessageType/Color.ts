/*
 * Copyright (c) 2010-2026 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2030-08-27
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

import MessageType from '../MessageType'

import type Message from '../Message'

interface ColorMessage extends Message {
	payload: {
		color1: number | null
		color2: number | null
	}
}

class Color extends MessageType {
	render(message: Message): DocumentFragment | false {
		if (message.isOwnMessage()) {
			return super.render(message)
		} else {
			return false
		}
	}

	shouldUpdateUserList(_message: Message): boolean {
		return true
	}

	preProcess(message: Message): void {
		this.profileStore.expire(message.userID)
	}
}

export = Color
