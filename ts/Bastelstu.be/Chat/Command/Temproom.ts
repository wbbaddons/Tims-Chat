/*
 * Copyright (c) 2010-2025 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2029-04-27
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

import type { IParser } from 'Bastelstu.be/parser-combinator'
import Command from '../Command'
import Parser from '../Parser'

interface CreatePayload {
	type: 'create'
}

interface InvitePayload {
	type: 'invite'
	username: string
}

interface DeletePayload {
	type: 'delete'
}

type Payload = CreatePayload | InvitePayload | DeletePayload

class Temproom extends Command {
	getParameterParser(): IParser<Payload> {
		const Create = Parser.C.string('create').thenReturns({
			type: 'create',
		} as CreatePayload)
		const Invite = Parser.C.string('invite')
			.thenLeft(Parser.Whitespace.rep())
			.thenRight(Parser.Username)
			.map((username): InvitePayload => {
				return { type: 'invite', username }
			})
		const Delete = Parser.C.string('delete').thenReturns({
			type: 'delete',
		} as DeletePayload)

		return Create.or(Invite).or(Delete)
	}

	*autocomplete(parameterString: string): Generator<string, void, undefined> {
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

export = Temproom
