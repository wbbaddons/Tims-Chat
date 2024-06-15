/*
 * Copyright (c) 2010-2024 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2028-06-15
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

define(['../Command', '../Parser'], function (Command, Parser) {
	'use strict'

	class Temproom extends Command {
		getParameterParser() {
			const Create = Parser.C.string('create').thenReturns({ type: 'create' })
			const Invite = Parser.C.string('invite')
				.thenLeft(Parser.Whitespace.rep())
				.thenRight(Parser.Username)
				.map((username) => {
					return { type: 'invite', username }
				})
			const Delete = Parser.C.string('delete').thenReturns({ type: 'delete' })

			return Create.or(Invite).or(Delete)
		}

		*autocomplete(parameterString) {
			const Create = Parser.C.string('create')
			const Invite = Parser.C.string('invite')
			const Delete = Parser.C.string('delete')

			const subcommandDone = Create.or(Invite)
				.or(Delete)
				.thenLeft(Parser.Whitespace)

			const subcommandCheck = subcommandDone.parse(
				Parser.Streams.ofString(parameterString)
			)
			if (subcommandCheck.isAccepted()) {
				return
			}

			yield* ['create', 'invite ', 'delete'].filter((item) =>
				item.startsWith(parameterString)
			)
		}
	}

	return Temproom
})
