/*
 * Copyright (c) 2010-2021 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2026-09-17
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

define(['WoltLabSuite/Core/Dom/Util'], function (DomUtil) {
	'use strict'

	const DEPENDENCIES = ['Template.UserList']
	class UserList {
		constructor(userListTemplate) {
			this.userListTemplate = userListTemplate
			this.chatUserList = elById('chatUserList')
		}

		bootstrap() {}

		render(users) {
			users.sort((a, b) => a.username.localeCompare(b.username))
			const html = this.userListTemplate.fetch({ users })
			const fragment = DomUtil.createFragmentFromHtml(html)

			// Replace the current user list with the new one
			const currentList = elBySel('#chatUserList > .boxContent > ul')
			const parentNode = currentList.parentNode
			parentNode.removeChild(currentList)
			parentNode.appendChild(fragment)
		}
	}
	UserList.DEPENDENCIES = DEPENDENCIES

	return UserList
})
