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

import console from './console'
import Ajax from 'Bastelstu.be/PromiseWrap/Ajax'
import Room from './Room'

import type {
	DatabaseObjectActionPayload,
	DatabaseObjectActionResponse,
	RequestOptions,
} from '@woltlab/d.ts/WoltLabSuite/Core/Ajax/Data'
import type Message from './Message'
import type { InstanceFactory } from 'Bastelstu.be/bottle'

interface MessageActionPullPayload
	extends Omit<DatabaseObjectActionPayload, 'className'> {
	parameters: {
		inLog: boolean
		from?: number
		to?: number
	}
}

interface MessageActionPullResponse extends DatabaseObjectActionResponse {
	returnValues: {
		messages: ReturnType<Message['toJSON']>[]
		from: number
		to: number
	}
}

const DEPENDENCIES = ['sessionID', 'Room', 'Message']
class Messenger {
	public static readonly DEPENDENCIES = DEPENDENCIES

	sessionID: string
	room: Room
	Message: InstanceFactory<Message, [ReturnType<Message['toJSON']>]>

	constructor(sessionID: string, room: Room, Message: Messenger['Message']) {
		if (!(room instanceof Room)) {
			throw new TypeError('You must pass a Room to the Messenger')
		}

		this.sessionID = sessionID
		this.room = room
		this.Message = Message
	}

	async pull(
		from: number = 0,
		to: number = 0,
		inLog: boolean = false
	): Promise<{ messages: Message[]; from: number; to: number }> {
		console.debug(`Messenger.pull`, 'from', from, 'to', to, 'inLog', inLog)

		const payload: MessageActionPullPayload = {
			actionName: 'pull',
			parameters: { inLog },
		}

		if (from !== 0 && to !== 0) {
			throw new Error('You must not set both from and to')
		}
		if (from !== 0) {
			payload.parameters.from = from
		}
		if (to !== 0) {
			payload.parameters.to = to
		}

		const data = (await Ajax.api(this, payload)) as MessageActionPullResponse
		const messages = Object.values(data.returnValues.messages).map((item) =>
			this.Message.instance(item)
		)
		const { from: newFrom, to: newTo } = data.returnValues

		return { messages, from: newFrom, to: newTo }
	}

	async push({
		commandID,
		parameters,
	}: {
		commandID: number
		parameters: Record<string, unknown>
	}): Promise<unknown> {
		const payload = {
			actionName: 'push',
			parameters: { commandID, parameters: JSON.stringify(parameters) },
		}

		return Ajax.api(this, payload)
	}

	async pushAttachment(tmpHash: string): Promise<unknown> {
		const payload = { actionName: 'pushAttachment', parameters: { tmpHash } }

		return Ajax.api(this, payload)
	}

	_ajaxSetup(): RequestOptions {
		return {
			silent: true,
			ignoreError: true,
			data: {
				className: 'chat\\data\\message\\MessageAction',
				parameters: { roomID: this.room.roomID, sessionID: this.sessionID },
			},
		}
	}
}

export = Messenger
