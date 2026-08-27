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

import Ajax from 'Bastelstu.be/PromiseWrap/Ajax'
import Confirmation from 'Bastelstu.be/PromiseWrap/Ui/Confirmation'

import type Message from '../../Message'
import type MessageStream from '../MessageStream'
import type { InstanceFactory } from 'Bastelstu.be/bottle'
import type { RequestOptions } from 'WoltLabSuite/Core/Ajax/Data'

const DEPENDENCIES = ['UiMessageStream', 'Message']
class Delete {
	public static readonly DEPENDENCIES = DEPENDENCIES

	messageStream: MessageStream
	Message: InstanceFactory<Message, [ReturnType<Message['toJSON']>]>

	constructor(messageStream: MessageStream, message: Delete['Message']) {
		this.messageStream = messageStream
		this.Message = message
	}

	bootstrap() {
		this.messageStream.on('ingested', (ev) => {
			this.bindListener(ev.detail)
		})
	}

	bindListener(detail: { message: Message; node: HTMLElement }[]): void {
		detail.forEach((item: { message: Message; node: HTMLElement }) => {
			if (!item) {
				return
			}

			const { node, message } = item
			const button = node.querySelector<HTMLElement>('.jsDeleteButton')
			if (!button) {
				return
			}

			button.addEventListener('click', (event): void => {
				event.preventDefault()

				void Confirmation.show({
					message: button.dataset.confirmMessageHtml!,
					messageIsHtml: true,
				})
					.then((_) => this.delete(message.messageID))
					.then((_) => {
						button.closest('li')!.remove()
					})
			})
		})
	}

	async delete(messageID: number): Promise<void> {
		{
			const payload = { objectIDs: [messageID] }

			await Ajax.api(this, payload)
		}

		{
			const objectType = 'be.bastelstu.chat.messageType.tombstone'
			const payload = { messageID, userID: null }

			// The tombstone message type only accesses `message.isDeleted` and `message.payload`
			const message = this.Message.instance({
				objectType,
				payload,
			} as any)
			message.getMessageType().render(message)
		}
	}

	_ajaxSetup(): RequestOptions {
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

export = Delete
