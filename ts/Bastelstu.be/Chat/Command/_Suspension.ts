/*
 * Copyright (c) 2010-2021 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2029-10-04
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
	duration: number | null
	reason: string | null
}

const DEPENDENCIES = ['ProfileStore']
class Suspension extends Command {
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
		const Forever = Parser.C.string('forever').thenReturns(null)
		const Timespan = Parser.N.digits
			.then(Parser.C.charIn('dhm'))
			.map(function ([span, unit]) {
				switch (unit) {
					case 'd':
						return span * 86400
					case 'h':
						return span * 3600
					case 'm':
						return span * 60
				}
				throw new Error('Unreachable')
			})
			.rep()
			.map((parts) => parts.array().reduce((carry, item) => carry + item, 0))
			.map((offset) => Math.floor(Date.now() / 1000) + offset)

		const Duration = Forever.or(Timespan).or(
			Parser.ISODate.map((item) => Math.floor(item.valueOf() / 1000))
		)

		return Parser.Username.thenLeft(Parser.Whitespace.rep())
			.then(
				Globally.thenLeft(Parser.Whitespace.rep())
					.thenReturns(true)
					.or(Parser.F.returns(false))
			)
			.then(Duration)
			.then(
				Parser.Whitespace.rep()
					.thenRight(Parser.Rest1)
					.or(Parser.F.eos.thenReturns(null))
			)
			.map(([username, globally, duration, reason]): Payload => {
				return { username, globally, duration, reason }
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
			if (/^[0-9]+$/.test(rest)) {
				yield `${prefix}${rest}h `
				yield `${prefix}${rest}d `
				yield `${prefix}${rest}m `
			}
			if (rest === '') {
				yield `${prefix}1h `
				yield `${prefix}1d `
				yield `${prefix}5m `
			}

			return
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

export = Suspension
