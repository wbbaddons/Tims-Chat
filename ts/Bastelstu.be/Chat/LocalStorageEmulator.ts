/*
 * Copyright (c) 2010-2025 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2029-02-08
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

class LocalStorageEmulator implements Storage {
	#data: Map<string, string>;

	[name: string]: unknown

	constructor() {
		this.#data = new Map()

		return new Proxy(this, {
			get(target, property): unknown {
				// Check if the property exists on the object or its prototype
				if (property in target) {
					return target[property as string]
				}

				// Otherwise proxy to the underlying map
				return target.getItem(property as string)
			},
			set(
				target,
				property: string | symbol,
				value: unknown,
				_receiver: unknown
			): boolean {
				// Check if the property exists on the object or its prototype
				if (property in target) {
					target[property as string] = value
				} else {
					// Proxy to the underlying map
					target.setItem(property as string, value as string)
				}
				return true
			},
			has(target, property) {
				return (
					property in target || // Check if the property exists on the object or its prototype
					target.#data.has(property as string) // check the underlying map
				)
			},
			ownKeys(target): string[] {
				// Proxy to the underlying map
				return Array.from(target.#data.keys())
			},
			getOwnPropertyDescriptor(_target, _property) {
				// Make the properties of the map visible
				return {
					enumerable: true,
					configurable: true,
				}
			},
		})
	}

	get length(): number {
		return this.#data.size
	}

	key(index: number = 0): string | null {
		return Array.from(this.#data.keys())[index]
	}

	getItem(key: string): string | null {
		const item = this.#data.get(key)
		// map undefined to null
		if (item == null) {
			return null
		}

		return item
	}

	setItem(key: string, value: string): void {
		// This function *should* only be called with strings as values,
		// but real localStorage accepts non-strings as well and converts them
		if (typeof (value as unknown) !== 'string') {
			this.#data.set(key, value.toString())
		} else {
			this.#data.set(key, value)
		}
	}

	removeItem(key: string): void {
		this.#data.delete(key)
	}

	clear(): void {
		this.#data.clear()
	}

	*[Symbol.iterator](): Generator<string, void, unknown> {
		yield* this.#data.values()
	}
}
export = LocalStorageEmulator
