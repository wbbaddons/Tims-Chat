/*
 * Copyright (c) 2010-2024 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2028-03-14
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

define([
	'WoltLabSuite/Core/Language',
	'../Helper',
	'../MessageType',
], function (Language, Helper, MessageType) {
	'use strict'

	const decorators = Symbol('decorators')

	class Info extends MessageType {
		constructor(...superArgs) {
			super(...superArgs)

			this[decorators] = new Set()
		}

		addDecorator(decorator) {
			if (typeof decorator !== 'function') {
				throw new TypeError('Supplied argument must be a function')
			}

			this[decorators].add(decorator)
		}

		getReferencedUsers(message) {
			return super
				.getReferencedUsers(message)
				.concat([message.payload.user.userID])
		}

		render(message) {
			const rooms = message.payload.rooms.map(function (item) {
				const aug = {
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
				Array.from(this[decorators]).reduce(
					(payload, decorator) => decorator(payload),
					Object.assign({}, message.payload, { rooms })
				)
			)

			const fragment = super.render(
				new Proxy(message, {
					get: function (target, property) {
						if (property === 'payload') return payload
						return target[property]
					},
				})
			)

			const button = document.createElement('button')
			button.classList.add('button', 'small', 'jsTooltip', 'hideIcon')
			button.setAttribute('title', Language.get('wcf.global.button.hide'))

			button.addEventListener('click', () => {
				button.closest('.chatMessageBoundary').hidden = true
			})

			const icon = document.createElement('fa-icon')
			icon.setIcon('xmark')

			button.append(icon)

			const elem = fragment.querySelector(
				'.chatMessage .containerList > li:first-child .containerHeadline'
			)
			elem.insertBefore(button, elem.firstChild)

			return fragment
		}
	}

	return Info
})
