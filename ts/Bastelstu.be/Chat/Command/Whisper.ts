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
import Parser from '../Parser'
import Plain from './Plain'

interface Payload {
	text: string
	username: string
}

class Whisper extends Plain {
	getParameterParser(): IParser<Payload> {
		return Parser.Username.thenLeft(Parser.Whitespace.rep())
			.then(super.getParameterParser())
			.map(
				([username, plain]): Payload => ({
					username,
					text: plain.text,
				})
			)
	}

	*autocomplete(parameterString: string): Generator<string, void, undefined> {
		const usernameDone = Parser.Username.thenLeft(Parser.Whitespace).parse(
			Parser.Streams.ofString(parameterString)
		)

		if (usernameDone.isAccepted()) {
			yield* super.autocomplete(parameterString)
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

export = Whisper
