/*
 * Copyright (c) 2010-2025 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2029-04-27
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 3
 * or later of the General Public License.
 */

import type { IParser } from 'Bastelstu.be/parser-combinator'
import Command from '../Command'
import Parser from '../Parser'

import type ProfileStore from '../ProfileStore'

interface Payload {
	username: string
	globally: boolean
}

const DEPENDENCIES = ['ProfileStore']
class Unsuspension extends Command {
	public static DEPENDENCIES = DEPENDENCIES

	profileStore: ProfileStore

	constructor(
		profileStore: ProfileStore,
		data: ConstructorParameters<typeof Command>[0]
	) {
		super(data)
		this.profileStore = profileStore
	}

	getParameterParser(): IParser<Payload> {
		const Globally = Parser.C.string('global').thenLeft(
			Parser.C.string('ly').opt()
		)

		return Parser.Username.then(
			Parser.Whitespace.rep()
				.thenRight(Globally.thenReturns(true))
				.or(Parser.F.returns(false))
		).map(([username, globally]): Payload => {
			return { username, globally }
		})
	}

	*autocomplete(parameterString: string): Generator<string, void, undefined> {
		const usernameDone = Parser.Username.thenLeft(Parser.Whitespace.rep()).map(
			(username) => `"${username.replace(/"/g, '""')}"`
		)
		const globallyDone = usernameDone
			.then(Parser.C.string('global').thenLeft(Parser.C.string('ly').opt()))
			.thenLeft(Parser.Whitespace.rep())

		const usernameCheck = usernameDone.parse(
			Parser.Streams.ofString(parameterString)
		)
		if (usernameCheck.isAccepted()) {
			const globallyCheck = globallyDone.parse(
				Parser.Streams.ofString(parameterString)
			)
			let prefix: string
			let rest: string
			if (globallyCheck.isAccepted()) {
				prefix = parameterString.substring(0, globallyCheck.offset)
				rest = parameterString.substring(globallyCheck.offset)
			} else {
				prefix = parameterString.substring(0, usernameCheck.offset)
				rest = parameterString.substring(usernameCheck.offset)
			}

			if (!globallyCheck.isAccepted() && 'globally'.startsWith(rest)) {
				yield `${prefix}globally `
			}
		}

		for (const userID of this.profileStore.getLastActivity()) {
			const user = this.profileStore.get(userID)!

			if (!user.username.startsWith(parameterString)) {
				continue
			}

			yield `"${user.username.replace(/"/g, '""')}" `
		}
	}
}

export = Unsuspension
