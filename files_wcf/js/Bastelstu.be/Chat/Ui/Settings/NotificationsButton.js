/*
 * Copyright (c) 2010-2024 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2028-06-15
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

define(['./ToggleButton'], function (ToggleButton) {
	'use strict'

	const DEPENDENCIES = ['UiNotification'].concat(
		ToggleButton.DEPENDENCIES || []
	)
	class NotificationsButton extends ToggleButton {
		constructor(element, notification, ...superDeps) {
			super(
				element,
				false,
				'Bastelstu.be/Chat/Ui/Settings/NotificationsButton',
				...superDeps
			)

			this.notification = notification
		}

		bootstrap() {
			super.bootstrap()

			// Hide the button if notifications are not supported or the permission has been denied
			if (
				!this.notification.systemSupported ||
				this.notification.systemDenied
			) {
				elRemove(this.element.closest('li'))
			}
		}

		enable() {
			super.enable()
			this.notification.enableSystemNotifications().catch((error) => {
				this.disable()

				if (this.notification.systemDenied) elRemove(this.element)
			})
		}

		disable() {
			super.disable()
			this.notification.disableSystemNotifications()
		}
	}
	NotificationsButton.DEPENDENCIES = DEPENDENCIES

	return NotificationsButton
})
