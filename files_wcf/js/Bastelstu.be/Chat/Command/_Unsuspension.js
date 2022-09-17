/*
 * Copyright (c) 2010-2021 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2026-09-17
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 3
 * or later of the General Public License.
 */

define(['../Command', '../Parser'], function (Command, Parser) {
	'use strict'

	const DEPENDENCIES = ['ProfileStore']
	class Unsuspension extends Command {
		constructor(profileStore, id) {
			super(id)
			this.profileStore = profileStore
		}

		getParameterParser() {
			const Globally = Parser.C.string('global').thenLeft(
				Parser.C.string('ly').opt()
			)

			return Parser.Username.then(
				Parser.Whitespace.rep()
					.thenRight(Globally.thenReturns(true))
					.or(Parser.F.returns(false))
			).map(([username, globally]) => {
				return { username, globally }
			})
		}

		*autocomplete(parameterString) {
			const usernameDone = Parser.Username.thenLeft(
				Parser.Whitespace.rep()
			).map((username) => `"${username.replace(/"/g, '""')}"`)
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
				let prefix, rest
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
				const user = this.profileStore.get(userID)
				if (!user.username.startsWith(parameterString)) continue

				yield `"${user.username.replace(/"/g, '""')}" `
			}
		}
	}
	Unsuspension.DEPENDENCIES = DEPENDENCIES

	return Unsuspension
})
