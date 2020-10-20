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

define([ '../Command'
       , '../Parser'
       , 'WoltLabSuite/Core/StringUtil'
       ], function (Command, Parser, StringUtil) {
	"use strict";

	const DEPENDENCIES = [ 'ProfileStore' ]
	class Plain extends Command {
		constructor(profileStore, id) {
			super(id)
			this.profileStore = profileStore
		}

		getParameterParser() {
			return Parser.Rest1
			.map(StringUtil.escapeHTML.bind(StringUtil))
			.map(text => ({ text }))
		}

		* autocomplete(parameterString) {
			const parts = parameterString.split(/ /)
			const lastWord = parts.pop().toLowerCase()

			if (lastWord === '') {
				return
			}

			for (const userID of this.profileStore.getLastActivity()) {
				const user = this.profileStore.get(userID)
				const username = user.username.toLowerCase()
				if (!username.startsWith(parameterString) && !username.startsWith(lastWord.replace(/^@/, ''))) continue

				yield `${parts.concat([ lastWord.startsWith('@') ? `@${user.username}` : user.username ]).join(' ')} `
			}
		}
	}
	Plain.DEPENDENCIES = DEPENDENCIES

	return Plain
});
