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

import * as Core from 'WoltLabSuite/Core/Core'
import LocalStorageEmulator from './LocalStorageEmulator'

const DEPENDENCIES = []
class LocalStorage {
	public static readonly DEPENDENCIES = DEPENDENCIES

	readonly #subprefix: string
	#storage: Storage | LocalStorageEmulator

	constructor(subprefix: string) {
		this.#subprefix = subprefix
		this.setStorageBackend(false)
	}

	private static isQuotaExceeded(error: unknown): boolean {
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

	private clearBackend(target: Storage | LocalStorageEmulator): void {
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

	private setStorageBackend(clear: boolean): void {
		const value = '__storage_test__'
		const key = `${this.storagePrefix}${value}`

		try {
			if (clear) {
				this.clearBackend(window.localStorage)
			}

			window.localStorage.setItem(key, value)
			window.localStorage.removeItem(key)
			this.#storage = window.localStorage
			return
		} catch (error) {
			console.error(`LocalStorage is unavailable:`, error)
		}

		try {
			if (clear) {
				this.clearBackend(window.sessionStorage)
			}

			window.sessionStorage.setItem(key, value)
			window.sessionStorage.removeItem(key)
			this.#storage = window.sessionStorage

			return
		} catch (error) {
			console.error(`SessionStorage is unavailable:`, error)
		}

		console.warn(`LocalStorage is unavailable, settings cannot be saved.`)
		this.#storage = new LocalStorageEmulator()
	}

	/**
	 * Return the prefix to use for the local storage
	 */
	get storagePrefix(): string {
		return `${Core.getStoragePrefix()}be.bastelstu.Chat.${this.#subprefix}`
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
		if (this.#storage instanceof LocalStorageEmulator) {
			try {
				// Try to clear the real localStorage and check if it is usable
				this.setStorageBackend(true)
				if (!(this.#storage instanceof LocalStorageEmulator)) {
					console.log('Switched back to using the LocalStorage')
				}
			} catch {
				/* no we can’t */
			}
		}

		this.clearBackend(this.#storage)
	}
}
export = LocalStorage
