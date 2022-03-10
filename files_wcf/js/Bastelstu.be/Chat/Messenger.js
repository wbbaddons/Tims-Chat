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

define(['./console', 'Bastelstu.be/PromiseWrap/Ajax', './Room'], function (
	console,
	Ajax,
	Room
) {
	'use strict'

	const DEPENDENCIES = ['sessionID', 'Room', 'Message']
	class Messenger {
		constructor(sessionID, room, Message) {
			if (!(room instanceof Room))
				throw new TypeError('You must pass a Room to the Messenger')

			this.sessionID = sessionID
			this.room = room
			this.Message = Message
		}

		async pull(from = 0, to = 0, inLog = false) {
			console.debug(`Messenger.pull`, 'from', from, 'to', to, 'inLog', inLog)

			const payload = { actionName: 'pull', parameters: { inLog } }
			if (from !== 0 && to !== 0) {
				throw new Error('You must not set both from and to')
			}
			if (from !== 0) payload.parameters.from = from
			if (to !== 0) payload.parameters.to = to

			const data = await Ajax.api(this, payload)
			const messages = Object.values(data.returnValues.messages).map((item) =>
				this.Message.instance(item)
			)
			const { from: newFrom, to: newTo } = data.returnValues

			return { messages, from: newFrom, to: newTo }
		}

		async push({ commandID, parameters }) {
			const payload = {
				actionName: 'push',
				parameters: { commandID, parameters: JSON.stringify(parameters) },
			}

			return Ajax.api(this, payload)
		}

		async pushAttachment(tmpHash) {
			const payload = { actionName: 'pushAttachment', parameters: { tmpHash } }

			return Ajax.api(this, payload)
		}

		_ajaxSetup() {
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
	Messenger.DEPENDENCIES = DEPENDENCIES

	return Messenger
})
