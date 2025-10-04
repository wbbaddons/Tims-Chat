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

import * as Language from 'WoltLabSuite/Core/Language'
import Helper from '../Helper'
import MessageType from '../MessageType'

import type Message from '../Message'

interface InfoMessage extends Message {
	payload: {
		rooms: {
			title: string
			roomID: number
			lastPush: number
			lastPull: number
			active: boolean
			link: string
		}[]
		away: boolean | null
		user: {
			userID: number
			username: string
			link: string
		}
	}
}

type Decorator = (
	payload: InfoMessage['payload']
) => InfoMessage['payload'] & Record<string, unknown>

class Info extends MessageType {
	#decorators: Set<Decorator>

	constructor(...superArgs: ConstructorParameters<typeof MessageType>) {
		super(...superArgs)

		this.#decorators = new Set()
	}

	addDecorator(decorator: Decorator) {
		if (typeof decorator !== 'function') {
			throw new TypeError('Supplied argument must be a function')
		}

		this.#decorators.add(decorator)
	}

	getReferencedUsers(message: InfoMessage) {
		return super
			.getReferencedUsers(message)
			.concat([message.payload.user.userID])
	}

	render(message: InfoMessage) {
		const rooms = message.payload.rooms.map(function (item) {
			const aug: {
				lastPull: Date | null
				lastPullHTML: string | null
				lastPush: Date | null
				lastPushHTML: string | null
			} = {
				lastPull: null,
				lastPullHTML: null,
				lastPush: null,
				lastPushHTML: null,
			}

			if (item.lastPull) {
				aug.lastPull = new Date(item.lastPull * 1000)
				aug.lastPullHTML = Helper.getTimeElementHTML(aug.lastPull)
			}

			if (item.lastPush) {
				aug.lastPush = new Date(item.lastPush * 1000)
				aug.lastPushHTML = Helper.getTimeElementHTML(aug.lastPush)
			}

			return Object.assign({}, item, aug)
		})

		const payload = Helper.deepFreeze(
			Array.from(this.#decorators).reduce(
				(payload, decorator) => decorator(payload),
				Object.assign({}, message.payload, { rooms })
			)
		)

		const fragment = super.render(
			new Proxy(message, {
				get: function (target, property) {
					if (property === 'payload') {
						return payload
					}

					return target[property] as unknown
				},
			})
		)

		if (fragment === false) {
			return false
		}

		const button = document.createElement('button')
		button.classList.add('button', 'small', 'jsTooltip', 'hideIcon')
		button.setAttribute('title', Language.get('wcf.global.button.hide'))

		button.addEventListener('click', () => {
			button.closest<HTMLElement>('.chatMessageBoundary')!.hidden = true
		})

		const icon = document.createElement('span')
		icon.classList.add('icon', 'icon16', 'fa-times')

		button.append(icon)

		const elem = fragment.querySelector(
			'.chatMessage .containerList > li:first-child .containerHeadline'
		)!
		elem.insertBefore(button, elem.firstChild)

		return fragment
	}
}

export = Info
