/*
 * Copyright (c) 2010-2024 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2028-01-13
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

define([
	'WoltLabSuite/Core/Date/Util',
	'WoltLabSuite/Core/Language',
	'WoltLabSuite/Core/Dom/Util',
	'Bastelstu.be/Chat/User',
], function (DateUtil, Language, DomUtil, User) {
	'use strict'

	const DEPENDENCIES = ['ProfileStore', 'Template']
	class MessageType {
		constructor(profileStore, templates, objectType) {
			this.profileStore = profileStore
			this.templates = templates

			this.objectType = objectType
		}

		shouldUpdateUserList() {
			return false
		}

		getReferencedUsers(message) {
			if (message.userID === null) return []

			return [message.userID]
		}

		preProcess(message) {}

		preRender(message) {}

		render(message) {
			const variables = {
				message,
				users: this.profileStore,
				author: this.profileStore.get(message.userID),
				DateUtil,
				Language,
			}

			if (variables.author == null) {
				variables.author = User.getGuest(message.username)
			}

			return DomUtil.createFragmentFromHtml(
				this.templates[message.objectType.replace(/\./g, '-')].fetch(variables)
			)
		}

		renderPlainText(message) {
			return false
		}

		joinable(messageA, messageB) {
			return false
		}
	}
	MessageType.DEPENDENCIES = DEPENDENCIES

	return MessageType
})
