/*
 * Copyright (c) 2010-2026 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2030-08-27
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

import Command from '../Command'
import Parser from '../Parser'
import * as StringUtil from 'WoltLabSuite/Core/StringUtil'

import type ProfileStore from '../ProfileStore'
import type { IParser } from 'Bastelstu.be/parser-combinator'

interface Payload {
	text: string
}

const DEPENDENCIES = ['ProfileStore']
class Plain extends Command {
	public static readonly DEPENDENCIES = DEPENDENCIES

	profileStore: ProfileStore

	constructor(
		profileStore: ProfileStore,
		data: ConstructorParameters<typeof Command>[0]
	) {
		super(data)
		this.profileStore = profileStore
	}

	getParameterParser(): IParser<Payload> {
		return Parser.Rest1.map((text) => StringUtil.escapeHTML(text)).map(
			(text) => ({ text })
		)
	}

	*autocomplete(parameterString: string): Generator<string, void, undefined> {
		const parts = parameterString.split(/ /)
		const lastWord = parts.pop()!.toLowerCase()

		if (lastWord === '') {
			return
		}

		for (const userID of this.profileStore.getLastActivity()) {
			const user = this.profileStore.get(userID)!
			const username = user.username.toLowerCase()
			if (
				!username.startsWith(parameterString) &&
				!username.startsWith(lastWord.replace(/^@/, ''))
			) {
				continue
			}

			yield `${parts
				.concat([
					lastWord.startsWith('@') ? `@${user.username}` : user.username,
				])
				.join(' ')} `
		}
	}
}

export = Plain
