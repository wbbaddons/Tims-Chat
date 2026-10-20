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

import ToggleButton from './ToggleButton'
import type Notification from '../Notification'

const DEPENDENCIES = ['UiNotification']
class NotificationsButton extends ToggleButton {
	public static readonly DEPENDENCIES = DEPENDENCIES

	notification: Notification

	constructor(element: Element, notification: Notification) {
		super(element, false, 'Bastelstu.be/Chat/Ui/Settings/NotificationsButton')

		this.notification = notification
	}

	bootstrap(): void {
		super.bootstrap()

		// Hide the button if notifications are not supported or the permission has been denied
		if (!this.notification.systemSupported || this.notification.systemDenied) {
			const parent = this.element.closest('li')
			parent?.remove()
		}
	}

	enable(): void {
		super.enable()

		this.notification.enableSystemNotifications().catch((_error) => {
			this.disable()

			if (this.notification.systemDenied) {
				this.element.remove()
			}
		})
	}

	disable(): void {
		super.disable()
		this.notification.disableSystemNotifications()
	}
}

export = NotificationsButton
