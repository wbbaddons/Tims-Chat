/*
 * Copyright (c) 2010-2025 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2029-10-04
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

import MessageType from '../MessageType'
import type Message from '../Message'

import type MessageStream from '../Ui/MessageStream'

interface TombstoneMessage extends Message {
	payload: {
		messageID: number
	}
}

const DEPENDENCIES = ['UiMessageStream'].concat(MessageType.DEPENDENCIES)
class Tombstone extends MessageType {
	public static readonly DEPENDENCIES = DEPENDENCIES

	messageStream: MessageStream

	constructor(
		messageStream: MessageStream,
		...superDeps: ConstructorParameters<typeof MessageType>
	) {
		super(...superDeps)

		this.messageStream = messageStream
	}

	render(message: TombstoneMessage): DocumentFragment | false {
		if (message.isDeleted) {
			return super.render(message)
		}

		const messageID = message.payload.messageID
		const node = document.getElementById(`message-${messageID}`)
		if (!node) {
			return false
		}

		node.classList.add('tombstone')

		const chatMessage = node.querySelector('.chatMessage')
		if (!chatMessage) {
			return false
		}

		const rendered = super.render(message)
		if (rendered == false) {
			return false
		}

		const oldIcon = node.querySelector('.chatMessageContent > .chatMessageIcon')
		const newIcon = rendered.querySelector('.chatMessageIcon')!

		if (oldIcon) {
			oldIcon.parentNode!.replaceChild(newIcon, oldIcon)
		} else {
			chatMessage.parentNode!.insertBefore(newIcon, chatMessage)
		}

		chatMessage.parentNode!.replaceChild(
			rendered.querySelector('.chatMessage')!,
			chatMessage
		)

		return false
	}
}

export = Tombstone
