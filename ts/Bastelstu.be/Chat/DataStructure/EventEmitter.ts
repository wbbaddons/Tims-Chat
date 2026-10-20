/*
 * Copyright (c) 2010-2026 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2030-10-20
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

type EventListener<T, D = unknown> = ({
	target,
	detail,
}: {
	target: T
	detail: D
}) => void

interface Listener<T> {
	listener: EventListener<T>
}
type EventSet<T> = Set<Listener<T>>
type EventMap<T> = Map<string, EventSet<T>>

interface EventEmitter<T> {
	on(type: string, listener: EventListener<T>): void
	emit(type: string, detail?: unknown): void
}

const listeners = new WeakMap<object, EventMap<unknown>>()
function EventEmitter<T extends object>(target: T) {
	Object.assign(target, {
		on(type: string, listener: EventListener<T>) {
			let eventMap = listeners.get(this)
			if (eventMap === undefined) {
				eventMap = new Map()
				listeners.set(this, eventMap)
			}

			let eventSet = eventMap.get(type)
			if (eventSet === undefined) {
				eventSet = new Set()
				eventMap.set(type, eventSet)
			}

			eventSet.add({ listener })
		},

		emit(type: string, detail = {}) {
			const set = listeners.get(this)?.get(type)

			if (set === undefined) {
				return
			}

			set.forEach(({ listener }) => {
				listener({ target: this, detail })
			})
		},
	})
}

export = EventEmitter
