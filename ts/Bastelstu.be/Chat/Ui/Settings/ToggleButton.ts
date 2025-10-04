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

import Button from './Button'
import LocalStorage from '../../LocalStorage'
import EventEmitter from '../../DataStructure/EventEmitter'

// Convince TypeScript that this class is an EventEmitter,
// this is done by calling EventEmitter(ToggleButton.prototype) after the class definition
// eslint-disable-next-line @typescript-eslint/no-unsafe-declaration-merging, @typescript-eslint/no-empty-object-type
interface ToggleButton extends EventEmitter<ToggleButton> {}

// eslint-disable-next-line @typescript-eslint/no-unsafe-declaration-merging
class ToggleButton extends Button {
	initialized: boolean
	storage: LocalStorage
	storageKey: string | undefined
	defaultState: boolean

	constructor(
		element: Element,
		defaultState: boolean,
		storageKey: string | undefined
	) {
		super(element)

		this.initialized = false
		this.storage = new LocalStorage('Settings.')

		this.storageKey = storageKey
		if (this.storageKey != null && this.storage.has(this.storageKey)) {
			defaultState = !!this.storage.get(this.storageKey)!
		}

		this.defaultState = defaultState
	}

	bootstrap(): void {
		super.bootstrap()

		if (this.defaultState) {
			this.enable()
		} else {
			this.disable()
		}
	}

	get enabled(): boolean {
		return this.element.classList.contains('active')
	}

	enable(): void {
		this.element.classList.add('active')

		if (this.storageKey != null) {
			this.storage.set(this.storageKey, true)
		}
	}

	disable(): void {
		this.element.classList.remove('active')

		if (this.storageKey != null) {
			this.storage.set(this.storageKey, false)
		}
	}

	onClick(event: MouseEvent): void {
		super.onClick(event)

		if (this.enabled) {
			this.disable()
		} else {
			this.enable()
		}
	}
}
EventEmitter(ToggleButton.prototype)

export = ToggleButton
