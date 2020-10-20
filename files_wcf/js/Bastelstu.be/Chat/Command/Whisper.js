/*
 * Copyright (c) 2010-2018 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2024-10-20
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

define([ '../Parser'
       , './Plain'
       ], function (Parser, Plain) {
	"use strict";

	class Whisper extends Plain {
		getParameterParser() {
			return Parser.Username.thenLeft(Parser.Whitespace.rep()).then(super.getParameterParser()).map(([ username, object ]) => {
				object.username = username

				return object
			})
		}

		* autocomplete(parameterString) {
			const usernameDone = Parser.Username.thenLeft(Parser.Whitespace).parse(Parser.Streams.ofString(parameterString))

			if (usernameDone.isAccepted()) {
				yield * super.autocomplete(parameterString)
				return
			}

			for (const userID of this.profileStore.getLastActivity()) {
				const user = this.profileStore.get(userID)
				if (!user.username.startsWith(parameterString)) continue

				yield `"${user.username.replace(/"/g, '""')}" `
			}
		}
	}

	return Whisper
});
