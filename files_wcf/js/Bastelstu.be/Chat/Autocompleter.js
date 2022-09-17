/*
 * Copyright (c) 2010-2021 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2026-09-17
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

define(['./CommandHandler', './Parser'], function (CommandHandler, Parser) {
	'use strict'

	const DEPENDENCIES = ['CommandHandler']
	class Autocompleter {
		constructor(commandHandler) {
			if (!(commandHandler instanceof CommandHandler))
				throw new TypeError(
					'You must pass a CommandHandler to the Autocompleter'
				)

			this.commandHandler = commandHandler
		}

		*autocomplete(text) {
			if (text === '/') {
				yield* this.autocompleteCommandTrigger(text, '')
				return
			}

			const [trigger, parameterString] = this.commandHandler.splitCommand(text)

			let command
			if (trigger === null) {
				command = this.commandHandler.getCommandByIdentifier(
					'be.bastelstu.chat',
					'plain'
				)
			} else {
				const triggerDone = Parser.Slash.thenRight(
					Parser.AlnumTrigger.or(Parser.SymbolicTrigger).thenLeft(
						Parser.Whitespace
					)
				).parse(Parser.Streams.ofString(text))
				if (!triggerDone.isAccepted()) {
					yield* this.autocompleteCommandTrigger(text, trigger)
					return
				}

				command = this.commandHandler.getCommandByTrigger(trigger)
			}

			if (command === null) {
				return
			}

			const values = command.autocomplete(parameterString)

			if (trigger !== null) {
				for (const item of values) {
					yield `/${trigger} ${item}`
				}
			} else {
				yield* values
			}
		}

		*autocompleteCommandTrigger(text, prefix) {
			const triggers = Array.from(this.commandHandler.getTriggers())

			triggers.sort()

			for (const trigger of triggers) {
				if (trigger === '') continue
				if (!trigger.startsWith(prefix)) continue
				if (!this.commandHandler.getCommandByTrigger(trigger).isAvailable)
					continue

				yield `/${trigger} `
			}
		}
	}
	Autocompleter.DEPENDENCIES = DEPENDENCIES

	return Autocompleter
})
