/*
 * Copyright (c) 2010-2018 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2024-11-08
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

define([], function () {
	'use strict'

	const listeners = new WeakMap()
	const EventEmitter = function (target) {
		Object.assign(target, {
			on(type, listener, options = {}) {
				if (!listeners.has(this)) {
					listeners.set(this, new Map())
				}
				if (!listeners.get(this).has(type)) {
					listeners.get(this).set(type, new Set())
				}

				if (!options.once) options.once = false
				listeners.get(this).get(type).add({ listener, options })
			},

			off(type, listener) {
				listeners.get(this).get(type).delete(listener)
			},

			emit(type, detail = {}) {
				if (!listeners.has(this)) return
				if (!listeners.get(this).has(type)) return

				const set = listeners.get(this).get(type)

				set.forEach(
					function ({ listener, options }) {
						if (options.once) {
							set.delete(listener)
						}

						listener({ target: this, detail })
					}.bind(this)
				)
			},
		})
	}

	return EventEmitter
})
