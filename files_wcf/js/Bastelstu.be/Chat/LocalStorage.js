/*
 * Copyright (c) 2010-2018 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2022-11-27
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

define([ 'WoltLabSuite/Core/Core', './LocalStorageEmulator' ], function (Core, LocalStorageEmulator) {
	'use strict';

	const DEPENDENCIES = [ ]
	class LocalStorage {
		constructor(subprefix) {
			this.subprefix = subprefix
			this.hasLocalStorage = false
			this.setupStorage()
		}

		static isQuotaExceeded(error) {
			return error instanceof DOMException && (
				// everything except Firefox
				error.code === 22 ||
				// Firefox
				error.code === 1014 ||
				// everything except Firefox
				error.name === 'QuotaExceededError' ||
				// Firefox
				error.name === 'NS_ERROR_DOM_QUOTA_REACHED')
		}

		static isAvailable() {
			try {
				const x = '__storage_test__'
				window.localStorage.setItem(x, x)
				window.localStorage.removeItem(x)
				return true
			}
			catch (error) {
				return LocalStorage.isQuotaExceeded(error)
			}
		}

		setupStorage() {
			if (LocalStorage.isAvailable()) {
				this.storage = window.localStorage
				this.hasLocalStorage = true
			}
			else {
				console.info('Falling back to in-memory local storage emulation')
				this.storage = new LocalStorageEmulator()
			}
		}

		/**
		 * Return the prefix to use for the local storage
		 *
		 * @returns {string} The storage prefix
		 */
		get storagePrefix() {
			let prefix = ''

			// WSC 3.1
			if (typeof Core.getStoragePrefix === 'function') {
				prefix = Core.getStoragePrefix()
			}

			return `${prefix}be.bastelstu.Chat.${this.subprefix}`
		}

		/**
		 * Calls listener, whenever key changes.
		 *
		 * @param {string} key The key to observe.
		 * @param {*} listener The listener to call.
		 */
		observe(key, listener) {
			window.addEventListener('storage', (event) => {
				if (event.storageArea !== window.localStorage) return
				if (event.key !== `${this.storagePrefix}${key}`) return

				listener(event)
			})
		}

		/**
		 * Sets the value of a setting
		 *
		 * @param   {string} key   The key of the setting to set
		 * @param   {string} value The new value of the setting
		 * @returns {string}
		 */
		set(key, value) {
			try {
				this.storage.setItem(`${this.storagePrefix}${key}`, JSON.stringify(value))
			}
			catch (error) {
				if (!LocalStorage.isQuotaExceeded(error)) throw error

				console.warn(`Your localStorage has exceeded the size quota for this domain`)
				console.warn(`We are falling back to an in-memory storage, this does not persist data!`)
				console.error(error)

				const storage = new LocalStorageEmulator()

				// Make a copy of the current localStorage
				for (let i = 0; i < localStorage.length; i++) {
					const key = localStorage.key(i)
					const value = localStorage.getItem(key)
					storage.setItem(key, value)
				}

				// Replace the localStorage with our in-memory variant
				this.storage = storage
			}

			return this.get(key)
		}

		/**
		 * Retrieves the value of a setting
		 *
		 * @param   {string} key The key of the setting to retrieve
		 * @returns {string}     The current value of the setting
		 */
		get(key) {
			const value = this.storage.getItem(`${this.storagePrefix}${key}`)

			if (value == null) return null
			return JSON.parse(value)
		}

		/**
		 * Returns whether the given setting has a value.
		 *
		 * @param   {string}  key The key of the setting to check
		 * @returns {boolean}
		 */
		has(key) {
			return this.storage.getItem(`${this.storagePrefix}${key}`) != null
		}

		/**
		 * Removes a single setting
		 *
		 * @param   {string} key The key of the setting to remove
		 * @returns {string}     The last value of the provided setting
		 */
		remove(key) {
			const value      = this.get(key)
			const storageKey = `${this.storagePrefix}${key}`

			this.storage.removeItem(storageKey)

			return value
		}

		/**
		 * Removes all of the chat settings with the right prefix
		 * and try to use the real localStorage again, if the qouta isn’t exceeded anymore
		 */
		clear() {
			const _clear = (target) => {
				for (let key in target) {
					if (!key.startsWith(this.storagePrefix) || !target.hasOwnProperty(key)) continue

					target.removeItem(key)
				}
			}

			if (this.hasLocalStorage && this.storage instanceof LocalStorageEmulator) {
				try {
					// Try to clear the real localStorage
					_clear(localStorage)

					// Check if we can use the localStorage again
					const x = '__storage_test__'
					window.localStorage.setItem(x, x)
					window.localStorage.removeItem(x)

					// It should be safe to use the localStorage again, as the storage
					// of this instance (given by the prefix) has been cleared completely
					this.storage = localStorage

					console.log('Switched back to using the localStorage')
				}
				catch (error) { /* no we can’t */ }
			}

			_clear(this.storage)
		}
	}
	LocalStorage.DEPENDENCIES = DEPENDENCIES

	return LocalStorage
});
