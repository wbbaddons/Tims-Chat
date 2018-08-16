/*
 * Copyright (c) 2010-2018 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2022-08-16
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

define([ 'WoltLabSuite/Core/Ui/Screen' ], function (UiScreen) {
	"use strict";

	const initialized = Symbol('initialized')

	class Mobile {
		constructor() {
			this[initialized] = false
		}

		bootstrap() {
			UiScreen.on('screen-md-down', { match:   this.enable.bind(this)
			                              , unmatch: this.disable.bind(this)
			                              , setup:   this.init.bind(this)
			                              })
		}

		init() {
			if (this[initialized]) return

			this[initialized] = true

			this.initQuickSettings()
		}

		enable() {

		}

		disable() {

		}

		initQuickSettings() {
			const navigation    = elBySel('#chatQuickSettingsNavigation > ul')
			const quickSettings = elById('chatQuickSettings')

			navigation.addEventListener(WCF_CLICK_EVENT, event => {
				event.stopPropagation()

				// mimic dropdown behavior
				window.setTimeout(() => {
					navigation.classList.remove('open')
				}, 10)
			})

			quickSettings.addEventListener(WCF_CLICK_EVENT, event => {
				event.preventDefault()
				event.stopPropagation()

				navigation.classList.toggle('open')
			})
		}
	}

	return Mobile
});
