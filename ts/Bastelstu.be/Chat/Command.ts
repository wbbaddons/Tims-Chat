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

import type { IParser } from 'Bastelstu.be/parser-combinator'
import Parser from './Parser'

interface CommandData {
	package: string
	identifier: string
	commandID: number
	module: string
	isAvailable: boolean
}

/**
 * Represents a chat command.
 */
class Command {
	#data: CommandData

	constructor(_data: CommandData) {
		this.#data = _data
	}

	getParameterParser(): IParser<any> {
		return Parser.Rest
	}

	*autocomplete(_parameterString: string): Generator<string, void, undefined> {
		// do nothing
	}

	get id(): number {
		return this.#data.commandID
	}

	get package(): string {
		return this.#data.package
	}

	get identifier(): string {
		return this.#data.identifier
	}

	get module(): string {
		return this.#data.module
	}

	get isAvailable(): boolean {
		return this.#data.isAvailable
	}
}

export = Command
