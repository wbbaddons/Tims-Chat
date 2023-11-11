/*
 * Copyright (c) 2010-2024 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2028-01-13
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

define([], function () {
	'use strict'

	class Throttler {
		constructor(callback, delay = 125) {
			if (!(typeof callback === 'function')) {
				throw new Error('Callback must be a function.')
			}
			if (delay < 0) {
				throw new Error('Delay must be non-negative.')
			}

			this.callback = callback
			this._delay = delay

			this.hot = false
			this.awaiting = false
			this.timer = null
			this.last = Date.now()
		}

		setTimer() {
			if (this.timer != null) {
				clearTimeout(this.timer)
			}

			this.timer = setTimeout((_) => {
				this.timer = null
				this.hot = false

				if (this.awaiting) {
					this.execute()
				}
			}, this.delay)
		}

		execute() {
			this.awaiting = false
			this.hot = true

			this.last = Date.now()

			this.setTimer()
			this.callback()
		}

		guardedExecute() {
			if (this.hot) {
				this.awaiting = true
			} else {
				this.execute()
			}
		}

		get delay() {
			return this._delay
		}

		set delay(newDelay) {
			if (this.awaiting && Date.now() - this.last > newDelay) {
				this._delay = 0
				this.setTimer()
			} else if (this.timer) {
				this._delay = Math.max(0, newDelay - (Date.now() - this.last))
				this.setTimer()
			}

			this._delay = newDelay
		}
	}

	const throttle = function (callback, delay = 125) {
		const throttler = new Throttler(callback, delay)
		const result = throttler.guardedExecute.bind(throttler)
		result.setDelay = function (newDelay) {
			throttler.delay = newDelay
		}
		result.getDelay = function () {
			return throttler.delay
		}

		return result
	}

	return throttle
})
