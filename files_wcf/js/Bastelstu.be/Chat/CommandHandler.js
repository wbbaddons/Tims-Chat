/*
 * Copyright (c) 2010-2018 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2022-08-16
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

define([ './Parser'
       , './ParseError'
       ], function (Parser, ParseError) {
	"use strict";

	const DEPENDENCIES = [ 'Trigger', 'Command' ]
	class CommandHandler {
		constructor(triggers, commands) {
			this.triggers = triggers
			this.commands = commands
		}

		splitCommand(input) {
			const result = Parser.Command.parse(Parser.Streams.ofString(input))

			if (result.isAccepted()) {
				return result.value
			}
			else {
				throw new ParseError('Empty trigger')
			}
		}

		applyCommand(command, parameterString) {
			const result = command.getParameterParser().parse(Parser.Streams.ofString(parameterString))

			if (result.isAccepted()) {
				return result.value
			}
			else {
				throw new ParseError('Could not parse', { result, parameterString })
			}
		}

		getTriggers() {
			return this.triggers.keys()
		}

		getCommandByTrigger(trigger) {
			const data = this.triggers.get(trigger)

			if (data == null) return null

			return this.getCommandByIdentifier(...data)
		}

		getCommandByIdentifier(packageName, identifier) {
			return this.commands[`${packageName.replace(/\./g, '-')}:${identifier}`]
		}
	}
	CommandHandler.DEPENDENCIES = DEPENDENCIES

	return CommandHandler
});
