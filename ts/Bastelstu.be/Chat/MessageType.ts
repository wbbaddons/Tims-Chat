/*
 * Copyright (c) 2010-2025 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2029-10-04
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

import * as DateUtil from 'WoltLabSuite/Core/Date/Util'
import * as Language from 'WoltLabSuite/Core/Language'
import DomUtil from 'WoltLabSuite/Core/Dom/Util'
import User from './User'

import type ProfileStore from './ProfileStore'
import type Template from './Template'
import type Message from './Message'

const DEPENDENCIES = ['ProfileStore', 'Template']
class MessageType {
	public static readonly DEPENDENCIES = DEPENDENCIES

	profileStore: ProfileStore
	templates: Record<string, Template>
	objectType: string

	constructor(
		profileStore: ProfileStore,
		templates: Record<string, Template>,
		objectType: string
	) {
		this.profileStore = profileStore
		this.templates = templates

		this.objectType = objectType
	}

	shouldUpdateUserList(_message: Message): boolean {
		return false
	}

	getReferencedUsers(message: Message): number[] {
		if (message.userID === null) {
			return []
		}

		return [message.userID]
	}

	preProcess(_message: Message): void {
		// do nothing
	}

	preRender(_message: Message): void {
		// do nothing
	}

	render(message: Message): DocumentFragment | false {
		const variables = {
			message,
			users: this.profileStore,
			author: this.profileStore.get(message.userID) as User | undefined,
			DateUtil,
			Language,
		}

		if (variables.author == null) {
			variables.author = User.getGuest(message.username)
		}

		const templateName = message.objectType.replace(/\./g, '-')
		const template = this.templates[templateName]

		return DomUtil.createFragmentFromHtml(template.fetch(variables))
	}

	renderPlainText(_message: Message): string | false {
		return false
	}

	joinable(_messageA: Message, _messageB: Message): boolean {
		return false
	}
}

export = MessageType
