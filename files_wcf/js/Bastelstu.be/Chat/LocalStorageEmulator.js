/*
 * Copyright (c) 2010-2021 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2027-02-22
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

define([], function () {
	'use strict'

	class LocalStorageEmulator {
		constructor() {
			this._data = new Map()
			return new Proxy(this, {
				get(target, property) {
					// Check if the property exists on the object or its prototype
					if (
						target.hasOwnProperty(property) ||
						Object.getPrototypeOf(target)[property]
					) {
						return target[property]
					}

					// Otherwise proxy to the underlying map
					return target.getItem(property)
				},
				set(target, property, value) {
					// Check if the property exists on the object or its prototype
					if (
						target.hasOwnProperty(property) ||
						Object.getPrototypeOf(target)[property]
					) {
						target[property] = value
					} else {
						// Proxy to the underlying map
						target.setItem(property, value)
					}
				},
				has(target, property) {
					return (
						target.hasOwnProperty(property) || // check the properties of the object
						Object.getPrototypeOf(target)[property] || // check its prototype
						target._data.has(property)
					) // check the underlying map
				},
				ownKeys(target) {
					// Proxy to the underlying map
					return Array.from(target._data.keys())
				},
				getOwnPropertyDescriptor(target, property) {
					// Make the properties of the map visible
					return {
						enumerable: true,
						configurable: true,
					}
				},
			})
		}

		get length() {
			return this._data.size
		}

		key(n = 0) {
			return Array.from(this._data.keys())[n]
		}

		getItem(key) {
			return this._data.get(key)
		}

		setItem(key, value) {
			this._data.set(key, value)
		}

		removeItem(key) {
			this._data.delete(key)
		}

		clear() {
			this._data.clear()
		}

		*[Symbol.iterator]() {
			yield* this._data.values()
		}
	}

	return LocalStorageEmulator
})
