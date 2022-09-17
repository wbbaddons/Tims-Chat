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

define([], function () {
	'use strict'

	const s = Symbol('s')
	const start = Symbol('start')

	class LRU {
		constructor() {
			this[s] = new Map()
			this[start] = undefined
		}

		add(value) {
			if (this[start] && this[start].value === value) {
				return
			}

			if (this[s].has(value)) {
				const entry = this[s].get(value)
				if (entry.prev) {
					entry.prev.next = entry.next
				}
				if (entry.next) {
					entry.next.prev = entry.prev
				}
			}
			const obj = { value, next: this[start], prev: undefined }
			this[start] = obj
			if (this[start].next) {
				this[start].next.prev = obj
			}
			this[s].set(value, obj)
		}

		*[Symbol.iterator]() {
			let current = this[start]
			do {
				yield current.value
			} while ((current = current.next))
		}
	}

	return LRU
})
