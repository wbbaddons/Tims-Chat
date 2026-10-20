/*
 * Copyright (c) 2010-2026 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2030-10-20
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

import type { IParser } from 'Bastelstu.be/parser-combinator'
import Command from '../Command'
import Parser from '../Parser'

import type ProfileStore from '../ProfileStore'

interface Payload {
	username: string
}

const DEPENDENCIES = ['ProfileStore']
class Info extends Command {
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
		return Parser.Username.map((username): Payload => ({ username }))
	}

	*autocomplete(parameterString: string): Generator<string, void, undefined> {
		for (const userID of this.profileStore.getLastActivity()) {
			const user = this.profileStore.get(userID)!
			if (!user.username.startsWith(parameterString)) {
				continue
			}

			yield `"${user.username.replace(/"/g, '""')}" `
		}
	}
}

export = Info
