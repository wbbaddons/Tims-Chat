/*
 * Copyright (c) 2010-2024 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2028-06-15
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

define(['../MessageType'], function (MessageType) {
	'use strict'

	const DEPENDENCIES = ['UiMessageStream'].concat(
		MessageType.DEPENDENCIES || []
	)
	class Tombstone extends MessageType {
		constructor(messageStream, ...superDeps) {
			super(...superDeps)

			this.messageStream = messageStream
		}

		render(message) {
			if (message.isDeleted) {
				return super.render(message)
			}

			const messageID = message.payload.messageID
			const node = elById(`message-${messageID}`)
			if (!node) return false

			node.classList.add('tombstone')

			const chatMessage = node.querySelector('.chatMessage')
			if (!chatMessage) return false

			const rendered = super.render(message)
			const oldIcon = node.querySelector(
				'.chatMessageContent > .chatMessageIcon'
			)
			const newIcon = rendered.querySelector('.chatMessageIcon')

			if (oldIcon) {
				oldIcon.parentNode.replaceChild(newIcon, oldIcon)
			} else {
				chatMessage.parentNode.insertBefore(newIcon, chatMessage)
			}

			chatMessage.parentNode.replaceChild(
				rendered.querySelector('.chatMessage'),
				chatMessage
			)

			return false
		}
	}
	Tombstone.DEPENDENCIES = DEPENDENCIES

	return Tombstone
})
