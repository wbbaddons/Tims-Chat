/*
 * Copyright (c) 2010-2026 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2030-10-20
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

import DomUtil from 'WoltLabSuite/Core/Dom/Util'

import type Template from '../Template'
import type User from '../User'

class UserList {
	public static readonly DEPENDENCIES = ['Template.UserList']

	userListTemplate: Template
	chatUserList: HTMLElement

	constructor(userListTemplate: Template) {
		this.userListTemplate = userListTemplate
		this.chatUserList = document.getElementById('chatUserList')!
	}

	bootstrap(): void {
		// Do nothing
	}

	render(users: User[]): void {
		users.sort((a, b) => a.username.localeCompare(b.username))
		const html = this.userListTemplate.fetch({ users })
		const fragment = DomUtil.createFragmentFromHtml(html)

		// Replace the current user list with the new one
		const currentList = this.chatUserList.querySelector(
			':scope > .boxContent > ul'
		)!
		const parentNode = currentList.parentNode!
		parentNode.removeChild(currentList)
		parentNode.appendChild(fragment)
	}
}

export = UserList
