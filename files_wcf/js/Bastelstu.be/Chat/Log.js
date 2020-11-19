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
	'./console',
	'Bastelstu.be/bottle',
	'WoltLabSuite/Core/Core',
	'./Message',
	'./Messenger',
	'./ProfileStore',
	'./Room',
	'./Template',
	'./Ui/Log',
	'./Ui/MessageStream',
	'./Ui/MessageActions/Delete',
], function (
	console,
	Bottle,
	Core,
	Message,
	Messenger,
	ProfileStore,
	Room,
	Template,
	Ui,
	UiMessageStream,
	UiMessageActionDelete
) {
	'use strict'

	const loader = Symbol('loader')

	class Log {
		constructor(params, config) {
			console.debug('ChatLog.constructor', 'Constructing …')

			this.config = config

			this.sessionID = Core.getUuid()
			this.bottle = new Bottle()
			this.bottle.value('bottle', this.bottle)
			this.bottle.value('config', config)
			this.bottle.constant('sessionID', this.sessionID)
			this.bottle.constant('roomID', params.roomID)

			// Register chat components
			this.service('Messenger', Messenger)
			this.service('ProfileStore', ProfileStore)
			this.service('Room', Room)

			// Register UI components
			this.service('Ui', Ui)
			this.service('UiMessageActionDelete', UiMessageActionDelete)
			this.service('UiMessageStream', UiMessageStream)

			// Register Models
			this.bottle.instanceFactory('Message', (container, m) => {
				return new Message(container.MessageType, m)
			})

			// Register Templates
			const selector = [
				'[type="x-text/template"]',
				'[data-application="be.bastelstu.chat"]',
				'[data-template-name]',
			].join('')
			const templates = elBySelAll(selector)
			templates.forEach(
				function (template) {
					this.bottle.factory(
						`Template.${elData(template, 'template-name')}`,
						function (container) {
							const includeNames = (elData(template, 'template-includes') || '')
								.split(/ /)
								.filter((item) => item !== '')
							const includes = {}
							includeNames.forEach((item) => (includes[item] = container[item]))

							return new Template(template.textContent, includes)
						}
					)
				}.bind(this)
			)

			// Register MessageTypes
			const messageTypes = Object.entries(this.config.messageTypes)
			messageTypes.forEach(([objectType, messageType]) => {
				const MessageType = require(messageType.module)

				this.bottle.factory(
					`MessageType.${objectType.replace(/\./g, '-')}`,
					(_) => {
						const deps = this.bottle.digest(MessageType.DEPENDENCIES || [])

						return new MessageType(...deps, objectType)
					}
				)
			})

			this.knows = { from: undefined, to: undefined }

			this.messageSinks = new Set()

			this.params = params

			this.pulling = false
		}

		service(name, _constructor, args = []) {
			this.bottle.factory(name, function (container) {
				const deps = (_constructor.DEPENDENCIES || []).map(
					(dep) => container[dep]
				)

				return new _constructor(...deps, ...args)
			})
		}

		async bootstrap() {
			console.debug('ChatLog.bootstrap', 'Initializing …')

			this.ui = this.bottle.container.Ui
			this.ui.bootstrap()

			this.registerMessageSink(this.bottle.container.UiMessageStream)

			if (this.params.messageID > 0) {
				await Promise.all([
					this.pull(undefined, this.params.messageID),
					this.pull(this.params.messageID + 1),
				])
			} else {
				await this.pull()
			}

			this.bottle.container.UiMessageStream.on(
				'nearTop',
				this.pullOlder.bind(this)
			)
			this.bottle.container.UiMessageStream.on(
				'reachedTop',
				this.pullOlder.bind(this)
			)
			this.bottle.container.UiMessageStream.on(
				'nearBottom',
				this.pullNewer.bind(this)
			)
			this.bottle.container.UiMessageStream.on(
				'reachedBottom',
				this.pullNewer.bind(this)
			)

			const element = document.querySelector(
				`#message-${this.params.messageID}`
			)

			// Force changing the hash to trigger a new lookup of the element.
			// At least Chrome won’t target an element if it is not in the DOM
			// on the initial page load with an hash set.
			window.location.hash = ''
			window.location.hash = `message-${this.params.messageID}`

			if (element && element.scrollIntoView) element.scrollIntoView()

			return this
		}

		registerMessageSink(sink) {
			if (typeof sink.ingest !== 'function') {
				throw new Error('The given sink does not provide a .ingest function.')
			}

			this.messageSinks.add(sink)
		}

		unregisterMessageSink(sink) {
			this.messageSinks.delete(sink)
		}

		async pull(from, to) {
			try {
				await this.handlePull(await this.performPull(from, to))
			} catch (e) {
				this.handleError(e)
			}
		}

		async pullOlder() {
			if (this.pulling) return
			if (this.knows.from <= 1) return

			this.pulling = true

			await this.pull(undefined, this.knows.from - 1)

			this.pulling = false
		}

		async pullNewer() {
			if (this.pulling) return

			this.pulling = true

			await this.pull(this.knows.to + 1)

			this.pulling = false
		}

		async performPull(from = undefined, to = undefined) {
			console.debug(
				'ChatLog.performPull',
				`Pulling new messages; from: ${
					from !== undefined ? from : 'undefined'
				}, to: ${to !== undefined ? to : 'undefined'}`
			)

			return this.bottle.container.Messenger.pull(from, to, true)
		}

		handleError(error) {
			console.debug('ChatLog.handleError', `Request failed`)
			console.debugException(error)
		}

		async handlePull(payload) {
			console.debug('ChatLog.handlePull', payload)

			// Null range: No messages satisfy the constraints
			if (payload.from > payload.to) return

			let messages = payload.messages

			if (this.knows.from !== undefined && this.knows.to !== undefined) {
				messages = messages.filter(
					function (message) {
						return !(
							this.knows.from <= message.messageID &&
							message.messageID <= this.knows.to
						)
					}.bind(this)
				)
			}

			if (this.knows.from === undefined || payload.from < this.knows.from)
				this.knows.from = payload.from
			if (this.knows.to === undefined || payload.to > this.knows.to)
				this.knows.to = payload.to

			await Promise.all(
				messages.map((message) => {
					return message.getMessageType().preProcess(message)
				})
			)

			const userIDs = messages
				.map((message) => message.userID)
				.filter((userID) => userID !== null)
			await this.bottle.container.ProfileStore.ensureUsersByIDs(userIDs)

			this.messageSinks.forEach((sink) => sink.ingest(messages))
		}
	}

	return Log
})
