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
	'../console',
	'../CommandHandler',
	'../LocalStorage',
	'../Messenger',
	'../ProfileStore',
	'WoltLabSuite/Core/Language',
	'WoltLabSuite/Core/Timer/Repeating',
], function (
	console,
	CommandHandler,
	LocalStorage,
	Messenger,
	ProfileStore,
	Language,
	RepeatingTimer
) {
	'use strict'

	const DEPENDENCIES = [
		'config',
		'CommandHandler',
		'Messenger',
		'ProfileStore',
		'UiInput',
	]
	class AutoAway {
		constructor(config, commandHandler, messenger, profileStore, input) {
			if (!(commandHandler instanceof CommandHandler))
				throw new TypeError('You must pass a CommandHandler to the AutoAway')
			if (!(messenger instanceof Messenger))
				throw new TypeError('You must pass a Messenger to the AutoAway')
			if (!(profileStore instanceof ProfileStore))
				throw new TypeError('You must pass a ProfileStore to the AutoAway')

			this.storage = new LocalStorage('AutoAway.')
			this.awayCommand = commandHandler.getCommandByIdentifier(
				'be.bastelstu.chat',
				'away'
			)
			if (this.awayCommand == null) {
				throw new Error('Unreachable')
			}
			this.config = config
			this.messenger = messenger
			this.input = input
			this.profileStore = profileStore
		}

		bootstrap() {
			if (this.config.autoAwayTime === 0) {
				return
			}
			if (!this.awayCommand.isAvailable) {
				return
			}

			this.timer = new RepeatingTimer(
				this.setAway.bind(this),
				this.config.autoAwayTime * 60e3
			)
			this.input.on(
				'input',
				(this.inputListener = (event) => {
					this.storage.set('channel', Date.now())
					this.reset()
				})
			)
			this.storage.observe('channel', this.reset.bind(this))
		}

		ingest(messages) {
			if (messages.some((message) => message.isOwnMessage())) this.reset()
		}

		reset() {
			console.debug('AutoAway.reset', `Resetting timer`)

			if (!this.timer) return

			this.timer.setDelta(this.config.autoAwayTime * 60e3)
		}

		async setAway() {
			console.debug('AutoAway.setAway', `Attempting to set as away`)

			if (this.storage.get('setAway') >= Date.now() - 10e3) {
				console.debug(
					'AutoAway.setAway',
					`setAway called within the last 10 seconds in another Tab`
				)
				return
			}
			this.storage.set('setAway', Date.now())

			if (this.profileStore.getSelf().away) {
				console.debug('AutoAway.setAway', `User is already away`)
				return
			}

			this.messenger.push({
				commandID: this.awayCommand.id,
				parameters: { reason: Language.get('chat.user.autoAway') },
			})
		}
	}
	AutoAway.DEPENDENCIES = DEPENDENCIES

	return AutoAway
})
