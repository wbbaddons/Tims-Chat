/*
 * Copyright (c) 2010-2025 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2029-01-19
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

import * as Core from 'WoltLabSuite/Core/Core'
import LocalStorageEmulator from './LocalStorageEmulator'

const DEPENDENCIES = []
class LocalStorage {
	public static readonly DEPENDENCIES = DEPENDENCIES

	readonly #subprefix: string
	#hasLocalStorage: boolean
	#storage: Storage | LocalStorageEmulator

	constructor(subprefix: string) {
		this.#subprefix = subprefix
		this.#hasLocalStorage = false
		this.setupStorage()
	}

	static isQuotaExceeded(error: unknown): boolean {
		return (
			error instanceof DOMException &&
			// everything except Firefox
			(error.code === 22 ||
				// Firefox
				error.code === 1014 ||
				// everything except Firefox
				error.name === 'QuotaExceededError' ||
				// Firefox
				error.name === 'NS_ERROR_DOM_QUOTA_REACHED')
		)
	}

	static isAvailable(): boolean {
		try {
			const x = '__storage_test__'
			window.localStorage.setItem(x, x)
			window.localStorage.removeItem(x)
			return true
		} catch (error) {
			return LocalStorage.isQuotaExceeded(error)
		}
	}

	setupStorage(): void {
		if (LocalStorage.isAvailable()) {
			this.#storage = window.localStorage
			this.#hasLocalStorage = true
		} else {
			console.info('Falling back to in-memory local storage emulation')
			this.#storage = new LocalStorageEmulator()
		}
	}

	/**
	 * Return the prefix to use for the local storage
	 */
	get storagePrefix(): string {
		let prefix = ''

		// WSC 3.1
		if (typeof Core.getStoragePrefix === 'function') {
			prefix = Core.getStoragePrefix()
		}

		return `${prefix}be.bastelstu.Chat.${this.#subprefix}`
	}

	/**
	 * Calls listener, whenever key changes.
	 */
	observe(key: string, listener: (event: StorageEvent) => void): void {
		window.addEventListener('storage', (event) => {
			if (event.storageArea !== window.localStorage) {
				return
			}
			if (event.key !== `${this.storagePrefix}${key}`) {
				return
			}

			listener(event)
		})
	}

	/**
	 * Sets the value of a setting
	 */
	set(key: string, value: unknown): unknown {
		try {
			this.#storage.setItem(
				`${this.storagePrefix}${key}`,
				JSON.stringify(value)
			)
		} catch (error) {
			if (!LocalStorage.isQuotaExceeded(error)) {
				throw error
			}

			console.warn(
				`Your localStorage has exceeded the size quota for this domain`
			)
			console.warn(
				`We are falling back to an in-memory storage, this does not persist data!`
			)
			console.error(error)

			const storage = new LocalStorageEmulator()

			// Make a copy of the current localStorage
			for (let i = 0; i < localStorage.length; i++) {
				const key = localStorage.key(i)

				if (key != null) {
					const value = localStorage.getItem(key)!
					storage.setItem(key, value)
				}
			}

			// Replace the localStorage with our in-memory variant
			this.#storage = storage
		}

		return this.get(key)
	}

	/**
	 * Retrieves the value of a setting
	 */
	get(key: string): unknown {
		const value = this.#storage.getItem(`${this.storagePrefix}${key}`)

		if (value == null) {
			return null
		}

		return JSON.parse(value)
	}

	/**
	 * Returns whether the given setting has a value.
	 */
	has(key: string): boolean {
		return this.#storage.getItem(`${this.storagePrefix}${key}`) != null
	}

	/**
	 * Removes a single setting
	 */
	remove(key: string): unknown {
		const value = this.get(key)
		const storageKey = `${this.storagePrefix}${key}`

		this.#storage.removeItem(storageKey)

		return value
	}

	/**
	 * Removes all of the chat settings with the right prefix
	 * and try to use the real localStorage again, if the qouta isn’t exceeded anymore
	 */
	clear(): void {
		const _clear = (target: Storage | LocalStorageEmulator) => {
			for (const key in target) {
				if (
					!key.startsWith(this.storagePrefix) ||
					!Object.prototype.hasOwnProperty.call(target, key)
				) {
					continue
				}

				target.removeItem(key)
			}
		}

		if (
			this.#hasLocalStorage &&
			this.#storage instanceof LocalStorageEmulator
		) {
			try {
				// Try to clear the real localStorage
				_clear(localStorage)

				// Check if we can use the localStorage again
				const x = '__storage_test__'
				window.localStorage.setItem(x, x)
				window.localStorage.removeItem(x)

				// It should be safe to use the localStorage again, as the storage
				// of this instance (given by the prefix) has been cleared completely
				this.#storage = localStorage

				console.log('Switched back to using the localStorage')
			} catch {
				/* no we can’t */
			}
		}

		_clear(this.#storage)
	}
}
export = LocalStorage
