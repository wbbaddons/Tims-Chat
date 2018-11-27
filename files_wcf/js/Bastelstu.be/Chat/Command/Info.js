/*
 * Copyright (c) 2010-2018 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2022-11-27
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

define([ '../Command'
       , '../Parser'
       ], function (Command, Parser) {
	"use strict";

	const DEPENDENCIES = [ 'ProfileStore' ]
	class Info extends Command {
		constructor(profileStore, id) {
			super(id)
			this.profileStore = profileStore
		}

		getParameterParser() {
			return Parser.Username.map(username => ({ username }))
		}

		* autocomplete(parameterString) {
			for (const userID of this.profileStore.getLastActivity()) {
				const user = this.profileStore.get(userID)
				if (!user.username.startsWith(parameterString)) continue

				yield `"${user.username.replace(/"/g, '""')}" `
			}
		}
	}
	Info.DEPENDENCIES = DEPENDENCIES

	return Info
});
