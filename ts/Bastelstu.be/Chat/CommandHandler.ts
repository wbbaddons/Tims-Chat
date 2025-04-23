/*
 * Copyright (c) 2010-2025 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2029-04-23
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

import Parser from './Parser'
import ParseError from './ParseError'

import Command from './Command'

type Trigger = string

const DEPENDENCIES = ['Trigger', 'Command']
class CommandHandler {
	public static readonly DEPENDENCIES = DEPENDENCIES

	triggers: Map<Trigger, [string, string]>
	commands: Partial<Record<string, Command>>

	constructor(
		triggers: Map<Trigger, [string, string]>,
		commands: Partial<Record<string, Command>>
	) {
		this.triggers = triggers
		this.commands = commands
	}

	splitCommand(input: string): [Trigger | null, string] {
		const result = Parser.Command.parse(Parser.Streams.ofString(input))

		if (result.isAccepted()) {
			return result.value
		} else {
			throw new ParseError('Empty trigger')
		}
	}

	applyCommand<T extends Command>(
		command: T,
		parameterString: string
	): unknown {
		const result = command
			.getParameterParser()
			.parse(Parser.Streams.ofString(parameterString))

		if (result.isAccepted()) {
			return result.value
		} else {
			throw new ParseError('Could not parse', { result, parameterString })
		}
	}

	getTriggers() {
		return this.triggers.keys()
	}

	getCommandByTrigger(trigger: Trigger): Command | null {
		const data = this.triggers.get(trigger)

		if (data == null) {
			return null
		}

		return this.getCommandByIdentifier(...data)
	}

	getCommandByIdentifier(
		packageName: string,
		identifier: string
	): Command | null {
		return (
			this.commands[`${packageName.replace(/\./g, '-')}:${identifier}`] ?? null
		)
	}
}

export = CommandHandler
