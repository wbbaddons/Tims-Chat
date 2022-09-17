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

define(['../console'], function (console) {
	'use strict'

	class ConnectionWarning {
		constructor() {
			this.warning = elById('chatConnectionWarning')
		}

		bootstrap() {}

		show() {
			elShow(this.warning)
			if (this.timeout) return

			console.debug('ConnectionWarning.show', 'Setting timeout')
			this.timeout = setTimeout((_) => {
				console.debug('ConnectionWarning.show', 'Timeout has passed')
				this.timeout = undefined

				if (this.autoHide) {
					console.debug('ConnectionWarning.show', 'Hiding connection warning')
					this.hide()
				}
			}, 10e3)
		}

		hide(force = false) {
			if (!this.timeout || force) {
				elHide(this.warning)
				window.clearTimeout(this.timeout)
			} else {
				console.debug(
					'ConnectionWarning.hide',
					'Automatically hiding after timeout has passed'
				)
				this.autoHide = true
			}
		}

		toggle() {
			elToggle(this.warning)
		}
	}

	return ConnectionWarning
})
