/*
 * Copyright (c) 2010-2021 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2026-03-04
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

define([
	'WoltLabSuite/Core/User',
	'WoltLabSuite/Core/StringUtil',
	'./Helper',
], function (CoreUser, StringUtil, Helper) {
	'use strict'

	const u = Symbol('user')

	/**
	 * Represents a user.
	 */
	class User {
		constructor(user) {
			this[u] = Helper.deepFreeze(user)

			Object.getOwnPropertyNames(this[u]).forEach((key) => {
				if (this[key]) {
					throw new Error('Attempting to override existing property')
				}

				Object.defineProperty(this, key, {
					value: this[u][key],
					enumerable: true,
				})
			})
		}

		get coloredUsername() {
			// No color
			if (this.color1 === null && this.color2 === null) return this.username

			// Single color
			if (this.color1 === this.color2)
				return `<span style="color: ${Helper.intToRGBHex(
					this.color1
				)};">${StringUtil.escapeHTML(this.username)}</span>`

			// Gradient
			const r1 = (this.color1 >> 16) & 0xff
			const r2 = (this.color2 >> 16) & 0xff
			const g1 = (this.color1 >> 8) & 0xff
			const g2 = (this.color2 >> 8) & 0xff
			const b1 = this.color1 & 0xff
			const b2 = this.color2 & 0xff

			const steps = this.username.length - 1
			const r = (r1 - r2) / steps
			const g = (g1 - g2) / steps
			const b = (b1 - b2) / steps

			return this[u].username
				.split('')
				.map((letter, index) => {
					const R = Math.round(r1 - index * r)
					const G = Math.round(g1 - index * g)
					const B = Math.round(b1 - index * b)

					return `<span style="color: rgb(${R}, ${G}, ${B})">${StringUtil.escapeHTML(
						letter
					)}</span>`
				})
				.join('')
		}

		get self() {
			return this.userID === CoreUser.userId
		}

		static getGuest(username) {
			const payload = { username, userID: null, color1: null, color2: null }

			return new User(payload)
		}

		wrap() {
			return { user: this[u] }
		}

		toJSON() {
			return this[u]
		}
	}

	return User
})
