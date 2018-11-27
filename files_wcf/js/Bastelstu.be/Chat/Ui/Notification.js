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

define([ 'WoltLabSuite/Core/Language' ], function (Language) {
	"use strict";

	const DEPENDENCIES = [ 'ProfileStore' ]
	class Notification {
		constructor(profileStore) {
			this.profileStore = profileStore

			this.unread = 0
			this.active = true
			this.browserTitle = document.title
			this.systemEnabled = false
			this.lastSeen = 0
		}

		bootstrap() {
			document.addEventListener('visibilitychange', this.onVisibilitychange.bind(this))
		}

		get systemSupported() {
			return "Notification" in window
		}

		get systemDenied() {
			return window.Notification.permission === 'denied'
		}

		get systemGranted() {
			if (this.systemDenied) {
				console.warn('[Notification]', 'System Notifications: permission denied')
			}

			return window.Notification.permission === 'granted'
		}

		onVisibilitychange() {
			this.active = !document.hidden

			if (this.active) {
				this.unread = 0
				this.updateBrowserTitle()
			}
		}

		ingest(messages) {
			if (!this.active) {
				messages.forEach(message => {
					const body = message.getMessageType().renderPlainText(message)

					if (body === false) return
					if (message.messageID < this.lastSeen) return

					this.lastSeen = message.messageID
					this.unread++

					if (this.systemEnabled && this.systemGranted) {
						// The user information is guaranteed to be cached at this point
						const user = this.profileStore.get(message.userID)
						const title = Language.get('chat.notification.title', { message })
						const options = { body
							        , icon:  user.imageUrl
							        , badge: user.imageUrl
							        }

						const notification = new window.Notification(title, options)

						setTimeout(notification.close.bind(notification), 5e3)
					}
				})
			}

			this.updateBrowserTitle()
		}

		updateBrowserTitle() {
			if (this.unread > 0) {
				document.title = `(${this.unread}) ${this.browserTitle}`
			}
			else {
				document.title = this.browserTitle
			}
		}

		enableSystemNotifications() {
			if (!this.systemSupported) return Promise.reject(new Error('Notifications are not supported'))

			if (this.systemGranted) {
				this.systemEnabled = true

				return Promise.resolve()
			}

			return new Promise((resolve, reject) => {
				window.Notification.requestPermission(permission => {
					this.systemEnabled = permission === 'granted'

					if (this.systemEnabled) {
						resolve()
					}
					else {
						reject(new Error(permission))
					}
				})
			})
		}

		disableSystemNotifications() {
			this.systemEnabled = false
		}
	}
	Notification.DEPENDENCIES = DEPENDENCIES

	return Notification
});
