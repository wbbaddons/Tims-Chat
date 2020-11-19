/*
 * Copyright (c) 2010-2018 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2024-11-20
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

define([
	'Bastelstu.be/Chat/Message',
	'Bastelstu.be/PromiseWrap/Ajax',
	'Bastelstu.be/PromiseWrap/Ui/Confirmation',
], function (Message, Ajax, Confirmation) {
	'use strict'

	const DEPENDENCIES = ['UiMessageStream', 'Message']
	class Delete {
		constructor(messageStream, message) {
			this.messageStream = messageStream
			this.Message = message
		}

		bootstrap() {
			this.messageStream.on('ingested', this.bindListener.bind(this))
		}

		bindListener({ detail }) {
			detail.forEach((item) => {
				if (!item) return

				const { node, message } = item
				const button = node.querySelector('.jsDeleteButton')
				if (!button) return

				button.addEventListener('click', async (event) => {
					event.preventDefault()

					await Confirmation.show({
						message: button.dataset.confirmMessageHtml,
						messageIsHtml: true,
					})

					await this.delete(message.messageID)

					elRemove(button.closest('li'))
				})
			})
		}

		async delete(messageID) {
			{
				const payload = { objectIDs: [messageID] }

				await Ajax.api(this, payload)
			}

			{
				const objectType = 'be.bastelstu.chat.messageType.tombstone'
				const payload = { messageID, userID: null }
				const message = this.Message.instance({ objectType, payload })
				message.getMessageType().render(message)
			}
		}

		_ajaxSetup() {
			return {
				silent: true,
				ignoreError: true,
				data: {
					className: 'chat\\data\\message\\MessageAction',
					actionName: 'trash',
				},
			}
		}
	}
	Delete.DEPENDENCIES = DEPENDENCIES

	return Delete
})
