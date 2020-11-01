/*
 * Copyright (c) 2010-2018 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2024-11-01
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

define([ './Button'
       , '../../LocalStorage'
       , '../../DataStructure/EventEmitter'
       ], function (Button, LocalStorage, EventEmitter) {
	'use strict';

	const DEPENDENCIES = [ ].concat(Button.DEPENDENCIES || [ ])
	class ToggleButton extends Button {
		constructor(element, defaultState, storageKey, ...superDeps) {
			super(element, ...superDeps)

			this.initialized = false
			this.storage = new LocalStorage('Settings.')

			this.storageKey = storageKey
			if (this.storage.has(this.storageKey)) {
				defaultState = this.storage.get(this.storageKey)
			}

			this.defaultState = defaultState
		}

		bootstrap() {
			super.bootstrap()

			if (this.defaultState) {
				this.enable()
			}
			else {
				this.disable()
			}
		}

		get enabled() {
			return this.element.classList.contains('active')
		}

		enable() {
			this.element.classList.add('active')

			if (this.storageKey != null) {
				this.storage.set(this.storageKey, true)
			}
		}

		disable() {
			this.element.classList.remove('active')

			if (this.storageKey != null) {
				this.storage.set(this.storageKey, false)
			}
		}

		onClick(event) {
			super.onClick(event)

			if (this.enabled) {
				this.disable()
			}
			else {
				this.enable()
			}
		}
	}
	EventEmitter(ToggleButton.prototype)
	ToggleButton.DEPENDENCIES = DEPENDENCIES

	return ToggleButton
});
