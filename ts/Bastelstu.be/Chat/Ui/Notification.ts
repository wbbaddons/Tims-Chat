/*
 * Copyright (c) 2010-2026 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2030-08-27
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

import * as Language from 'WoltLabSuite/Core/Language'

import type ProfileStore from '../ProfileStore'
import type Message from '../Message'

const DEPENDENCIES = ['ProfileStore']
class Notification {
	public static readonly DEPENDENCIES = DEPENDENCIES

	profileStore: ProfileStore

	unread: number
	active: boolean
	browserTitle: string
	systemEnabled: boolean
	lastSeen: number

	constructor(profileStore: ProfileStore) {
		this.profileStore = profileStore

		this.unread = 0
		this.active = true
		this.browserTitle = document.title
		this.systemEnabled = false
		this.lastSeen = 0
	}

	bootstrap(): void {
		document.addEventListener('visibilitychange', () => {
			this.onVisibilitychange()
		})
	}

	get systemSupported(): boolean {
		return 'Notification' in window
	}

	get systemDenied(): boolean {
		return window.Notification.permission === 'denied'
	}

	get systemGranted(): boolean {
		if (this.systemDenied) {
			console.warn('[Notification]', 'System Notifications: permission denied')
		}

		return window.Notification.permission === 'granted'
	}

	onVisibilitychange(): void {
		this.active = !document.hidden

		if (this.active) {
			this.unread = 0
			this.updateBrowserTitle()
		}
	}

	ingest(messages: Message[]): void {
		if (!this.active) {
			messages.forEach((message) => {
				const body = message.getMessageType().renderPlainText(message)

				if (body === false) {
					return
				}
				if (message.messageID < this.lastSeen) {
					return
				}

				this.lastSeen = message.messageID
				this.unread++

				if (this.systemEnabled && this.systemGranted) {
					// The user information is guaranteed to be cached at this point
					const user = this.profileStore.get(message.userID)!
					const title = Language.get('chat.notification.title', { message })
					const options = { body, icon: user.imageUrl, badge: user.imageUrl }

					const notification = new window.Notification(title, options)

					setTimeout(notification.close.bind(notification), 5e3)
				}
			})
		}

		this.updateBrowserTitle()
	}

	updateBrowserTitle(): void {
		if (this.unread > 0) {
			document.title = `(${this.unread}) ${this.browserTitle}`
		} else {
			document.title = this.browserTitle
		}
	}

	async enableSystemNotifications(): Promise<void> {
		if (!this.systemSupported) {
			throw new Error('Notifications are not supported')
		}

		if (this.systemGranted) {
			this.systemEnabled = true
			return
		}

		const permission = await window.Notification.requestPermission()
		this.systemEnabled = permission === 'granted'
		if (!this.systemEnabled) {
			throw new Error(permission)
		}
	}

	disableSystemNotifications(): void {
		this.systemEnabled = false
	}
}

export = Notification
