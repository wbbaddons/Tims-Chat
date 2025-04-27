/*
 * Copyright (c) 2010-2025 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2029-04-27
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

import * as UiScreen from 'WoltLabSuite/Core/Ui/Screen'

class Mobile {
	#initialized: boolean

	constructor() {
		this.#initialized = false
	}

	bootstrap() {
		UiScreen.on('screen-md-down', {
			match: () => {
				this.enable()
			},
			unmatch: () => {
				this.disable()
			},
			setup: () => {
				this.init()
			},
		})
	}

	init() {
		if (this.#initialized) {
			return
		}

		this.#initialized = true

		this.initQuickSettings()
	}

	enable(): void {
		// do nothing
	}

	disable(): void {
		// do nothing
	}

	initQuickSettings() {
		const navigation = document.querySelector(
			'#chatQuickSettingsNavigation > ul'
		)!
		const quickSettings = document.getElementById('chatQuickSettings')!

		navigation.addEventListener('click', (event) => {
			event.stopPropagation()

			// mimic dropdown behavior
			window.setTimeout(() => {
				navigation.classList.remove('open')
			}, 10)
		})

		quickSettings.addEventListener('click', (event) => {
			event.preventDefault()
			event.stopPropagation()

			navigation.classList.toggle('open')
		})
	}
}

export = Mobile
