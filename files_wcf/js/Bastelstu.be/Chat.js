/*
 * Copyright (c) 2010-2020 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2024-11-01
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

define([ './Chat/console'
       , 'Bastelstu.be/bottle'
       , 'Bastelstu.be/_Push'
       , 'WoltLabSuite/Core/Core'
       , 'WoltLabSuite/Core/Language'
       , 'WoltLabSuite/Core/Timer/Repeating'
       , 'WoltLabSuite/Core/User'
       , './Chat/Autocompleter'
       , './Chat/CommandHandler'
       , './Chat/DataStructure/Throttle'
       , './Chat/Message'
       , './Chat/Messenger'
       , './Chat/ParseError'
       , './Chat/ProfileStore'
       , './Chat/Room'
       , './Chat/Template'
       , './Chat/Ui/Attachment/Upload'
       , './Chat/Ui/AutoAway'
       , './Chat/Ui/Chat'
       , './Chat/Ui/ConnectionWarning'
       , './Chat/Ui/ErrorDialog'
       , './Chat/Ui/Input'
       , './Chat/Ui/Input/Autocompleter'
       , './Chat/Ui/MessageStream'
       , './Chat/Ui/MessageActions/Delete'
       , './Chat/Ui/Mobile'
       , './Chat/Ui/Notification'
       , './Chat/Ui/ReadMarker'
       , './Chat/Ui/Settings'
       , './Chat/Ui/Topic'
       , './Chat/Ui/UserActionDropdownHandler'
       , './Chat/Ui/UserList'
       ], function (console, Bottle, Push, Core, Language, RepeatingTimer, CoreUser, Autocompleter,
                    CommandHandler, Throttle, Message, Messenger, ParseError, ProfileStore, Room, Template, UiAttachmentUpload, UiAutoAway, Ui,
                    UiConnectionWarning, ErrorDialog, UiInput, UiInputAutocompleter, UiMessageStream, UiMessageActionDelete, UiMobile, UiNotification,
                    UiReadMarker, UiSettings, UiTopic, UiUserActionDropdownHandler, UiUserList) {
	"use strict";

	class Chat {
		constructor(roomID, config) {
			console.debug('Chat.constructor', 'Constructing …')

			this.config         = config

			this.sessionID      = Core.getUuid()

			// Setup Bottle containers
			this.bottle         = new Bottle()
			this.bottle.value('bottle', this.bottle)
			this.bottle.value('config', config)
			this.bottle.constant('sessionID', this.sessionID)
			this.bottle.constant('roomID', roomID)

			// Register chat components
			this.service('Autocompleter', Autocompleter)
			this.service('CommandHandler', CommandHandler)
			this.service('Messenger', Messenger)
			this.service('ProfileStore', ProfileStore)
			this.service('Room', Room)

			// Register UI components
			this.service('Ui', Ui)
			this.service('UiAutoAway', UiAutoAway)
			this.service('UiConnectionWarning', UiConnectionWarning)
			this.service('UiInput', UiInput)
			this.service('UiInputAutocompleter', UiInputAutocompleter)
			this.service('UiMessageActionDelete', UiMessageActionDelete)
			this.service('UiMessageStream', UiMessageStream)
			this.service('UiMobile', UiMobile)
			this.service('UiNotification', UiNotification)
			this.service('UiReadMarker', UiReadMarker)
			this.service('UiSettings', UiSettings)
			this.service('UiTopic', UiTopic)
			this.service('UiUserActionDropdownHandler', UiUserActionDropdownHandler)
			this.service('UiUserList', UiUserList)
			this.service('UiAttachmentUpload', UiAttachmentUpload)

			// Register Models
			this.bottle.instanceFactory('Message', (container, m) => {
				return new Message(container.MessageType, m)
			})

			// Register Templates
			const selector = [ '[type="x-text/template"]'
			                 , '[data-application="be.bastelstu.chat"]'
			                 , '[data-template-name]'
			                 ].join('')

			const templates = elBySelAll(selector)

			Array.prototype.forEach.call(templates, (function (template) {
				this.bottle.factory(`Template.${template.dataset.templateName}`, function (container) {
					const includeNames = (template.dataset.templateIncludes || '').split(/ /).filter(item => item !== "")
					const includes = { }
					includeNames.forEach(item => includes[item] = container[item])

					return new Template(template.textContent, includes)
				})
			}).bind(this))

			// Register MessageTypes
			Object.entries(this.config.messageTypes)
			.forEach(([ objectType, messageType ]) => {
				const MessageType = require(messageType.module)

				this.bottle.factory(`MessageType.${objectType.replace(/\./g, '-')}`, _ => {
					const deps = this.bottle.digest(MessageType.DEPENDENCIES || [])

					return new MessageType(...deps, objectType)
				})
			})

			// Register Commands
			Object.values(this.config.commands).forEach(command => {
				const Command = require(command.module)

				this.bottle.factory(`Command.${command.package.replace(/\./g, '-')}:${command.identifier}`, _ => {
					const deps = this.bottle.digest(Command.DEPENDENCIES || [])

					return new Command(...deps, command)
				})
			})
			this.bottle.constant('Trigger', new Map(Object.entries(this.config.triggers).map(([ trigger, commandID ]) => {
				const command = this.config.commands[commandID]
				const key = [ command.package, command.identifier ]
				return [ trigger, key ]
			})))

			// Register Settings
			Array.from(elBySelAll('#chatQuickSettingsNavigation .button[data-module]')).forEach(item => {
				const Button = require(item.dataset.module)

				this.bottle.instanceFactory(`UiSettingsButton.${item.dataset.module.replace(/\./g, '-')}`, (_, element) => {
					const deps   = this.bottle.digest(Button.DEPENDENCIES || [])
					return new Button(element, ...deps)
				})
			})

			this.knows  = { from: undefined
			              , to:   undefined
			              }

			this.processMessagesThrottled = Throttle(this.processMessages.bind(this))
			this.queuedMessages = [ ]
			this.messageSinks = new Set()

			this.pullTimer         = undefined
			this.pullUserListTimer = undefined
			this.pushConnected     = false

			this.firstFailure = null
		}

		service(name, _constructor, args = [ ]) {
			this.bottle.factory(name, _ => {
				const deps = this.bottle.digest(_constructor.DEPENDENCIES || [ ])

				return new _constructor(...deps, ...args)
			})
		}

		async bootstrap() {
			console.debug('Chat.bootstrap', 'Initializing …')

			this.ui = this.bottle.container.Ui
			this.ui.bootstrap()

			this.ui.input.on('submit', this.onSubmit.bind(this))
			this.ui.input.on('autocomplete', this.onAutocomplete.bind(this))
			this.ui.attachmentUpload.on('send', (event) => {
				event.detail.promise = this.onSendAttachment(event)
			})

			await this.bottle.container.Room.join()

			// Bind unload event to leave the Chat
			window.addEventListener('unload', this.bottle.container.Room.leave.bind(this.bottle.container.Room, true))
			document.addEventListener('visibilitychange', _ => {
				this.processMessagesThrottled.setDelay(document.hidden ? 10000 : 125)
			})

			this.pullTimer = new RepeatingTimer(Throttle(this.pullMessages.bind(this)), this.config.reloadTime * 1e3)

			Push.onConnect(_ => {
				console.debug('Chat.bootstrap', 'Push connected')
				this.pushConnected = true
				this.pullTimer.setDelta(30e3)
			})
			.catch(error => { console.debug(error) })

			Push.onDisconnect(_ => {
				console.debug('Chat.bootstrap', 'Push disconnected')
				this.pushConnected = false
				this.pullTimer.setDelta(this.config.reloadTime * 1e3)
			})
			.catch(error => { console.debug(error) })

			Push.onMessage('be.bastelstu.chat.message', this.pullMessages.bind(this))
			.catch(error => { console.debug(error) })

			// Fetch user list every 60 seconds
			// This acts as a safety net: It should be kept current by messages whenever possible.
			this.pullUserListTimer = new RepeatingTimer(this.updateUsers.bind(this), 60e3)

			this.registerMessageSink(this.bottle.container.UiMessageStream)
			this.registerMessageSink(this.bottle.container.UiNotification)
			this.registerMessageSink(this.bottle.container.UiAutoAway)

			await Promise.all([ this.pullMessages()
			                  , this.updateUsers()
			                  , this.bottle.container.ProfileStore.ensureUsersByIDs([ CoreUser.userId ])
			                  ])

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

		hcf(err = undefined) {
			console.debug('Chat.hcf', 'Gotcha! FIRE was caught! FIRE’s data was newly added to the POKéDEX.', err)

			this.pullTimer.stop()
			this.pullUserListTimer.stop()

			new ErrorDialog(Language.get('chat.error.hcf', { err }))
		}

		async onSubmit(event) {
			const input = event.target
			const value = input.getText()

			console.debug('Chat.onSubmit', `Pushing message: ${value}`)

			// Clear message input
			input.insertText('', { append: false })

			this.markAsBack()

			let [ trigger, parameterString ] = this.bottle.container.CommandHandler.splitCommand(value)
			let command = null
			if (trigger === null) {
				command = this.bottle.container.CommandHandler.getCommandByIdentifier('be.bastelstu.chat', 'plain')
			}
			else {
				command = this.bottle.container.CommandHandler.getCommandByTrigger(trigger)
			}

			if (command === null) {
				this.ui.input.inputError(Language.get('chat.error.triggerNotFound', { trigger }))
				return
			}

			try {
				let parameters
				try {
					parameters = this.bottle.container.CommandHandler.applyCommand(command, parameterString)
				}
				catch (e) {
					if (e instanceof ParseError) {
						e = new Error(Language.get('chat.error.invalidParameters', { data: e.data }))
					}
					throw e
				}

				const payload = { commandID: command.id
				                , parameters
				                }

				try {
					await this.bottle.container.Messenger.push(payload)
					this.ui.input.hideInputError()
				}
				catch (error) {
					let seriousError = true
					if (error.returnValues && error.returnValues.fieldName === 'message' && (error.returnValues.realErrorMessage || error.returnValues.errorType)) {
						this.ui.input.inputError(error.returnValues.realErrorMessage || error.returnValues.errorType)
						seriousError = false
					}
					else {
						this.ui.input.inputError(error.message)
					}

					if (seriousError) {
						this.handleError(error)
					}
				}

				// We assume that a running push server will push us our own message
				if (!this.pushConnected) {
					this.pullMessages()
				}

				console.debug('Chat.onSubmit', `Done`)
			}
			catch (e) {
				this.ui.input.inputError(e.message)
			}
		}

		async markAsBack() {
			try {
				if (this.bottle.container.ProfileStore.getSelf().away == null) return
				console.debug('Chat.markAsBack', `Marking as back`)

				const command = this.bottle.container.CommandHandler.getCommandByIdentifier('be.bastelstu.chat', 'back')
				return this.bottle.container.Messenger.push({ commandID: command.id, parameters: { } })
			}
			catch (err) {
				console.error('Chat.markAsBack', err)
			}
		}

		async onSendAttachment(event) {
			return this.bottle.container.Messenger.pushAttachment(event.detail.attachmentId)
		}

		onAutocomplete(event) {
			const input = event.target
			const value = input.getText(true)

			console.debug('Chat.onAutocomplete', `Autocompleting message: ${value}`)

			const result = this.bottle.container.Autocompleter.autocomplete(value)
			const completions = []
			for (const item of result) {
				completions.push(item)
				if (completions.length == 5) break
			}

			this.ui.autocompleter.sendCompletions(completions)
		}

		async pullMessages() {
			console.debug('Chat.pullMessages', `Pulling new messages, starting at ${this.knows.to ? this.knows.to + 1 : ''}`)

			let payload
			try {
				if (this.knows.to === undefined) {
					payload = await this.bottle.container.Messenger.pull()
				}
				else {
					payload = await this.bottle.container.Messenger.pull(this.knows.to + 1)
				}
			}
			catch (e) {
				this.handleError(e)
				return
			}

			console.debug('Chat.pullMessages', `Handling result: `, payload)
			const start = (performance ? performance : Date).now()
			this.ui.connectionWarning.hide()
			this.firstFailure = null

			// Null range: No messages satisfy the constraints
			if (payload.from > payload.to) {
				const end = (performance ? performance : Date).now()
				console.debug('Chat.pullMessages', `took ${(end - start) / 1000}s`)
				return
			}

			let messages = payload.messages

			if (this.knows.from !== undefined && this.knows.to !== undefined) {
				messages = messages.filter((message) => {
					return !(this.knows.from <= message.messageID && message.messageID <= this.knows.to)
				})
			}

			if (this.knows.from === undefined || payload.from < this.knows.from) this.knows.from = payload.from
			if (this.knows.to === undefined || payload.to > this.knows.to) this.knows.to = payload.to

			this.queuedMessages.push(messages)
			const end = (performance ? performance : Date).now()
			console.debug('Chat.pullMessages', `took ${(end - start) / 1000}s`)

			this.processMessagesThrottled()
		}

		handleError(error) {
			if (this.firstFailure === null) {
				console.error('Chat.handleError', `Request failed, 30 seconds until shutdown`)
				this.firstFailure = Date.now()
				this.ui.connectionWarning.show()
			}

			console.debugException(error)

			if ((Date.now() - this.firstFailure) >= 30e3) {
				console.error('Chat.handleError', ' Failures for 30 seconds, aborting')

				this.hcf(error)
			}
		}

		async processMessages() {
			console.debug('Chat.processMessages', 'Processing messages')
			const start = (performance ? performance : Date).now()
			const messages = [ ].concat(...this.queuedMessages)
			this.queuedMessages = []

			if (messages.length === 0) return

			await Promise.all(messages.map(async (message) => {
				this.bottle.container.ProfileStore.pushLastActivity(message.userID)

				return message.getMessageType().preProcess(message)
			}))

			const updateUserList = messages.some((message) => {
				return message.getMessageType().shouldUpdateUserList(message)
			})

			if (updateUserList) {
				this.updateUsers()
			}

			await this.bottle.container.ProfileStore.ensureUsersByIDs([ ].concat(...messages.map(message => message.getMessageType().getReferencedUsers(message))))

			messages.forEach((message) => {
				message.getMessageType().preRender(message)
			})

			this.messageSinks.forEach(sink => sink.ingest(messages))
			const end = (performance ? performance : Date).now()
			console.debug('Chat.processMessages', `took ${(end - start) / 1000}s`)
		}

		async updateUsers() {
			console.debug('Chat.updateUsers')

			const users = await this.bottle.container.Room.getUsers()
			await this.bottle.container.ProfileStore.ensureUsersByIDs(users.map(user => user.userID))
			this.ui.userList.render(users)
		}
	}

	return Chat
});
