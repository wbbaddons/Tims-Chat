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

import CoreUser from 'WoltLabSuite/Core/User'
import * as StringUtil from 'WoltLabSuite/Core/StringUtil'
import Helper from './Helper'

const u = Symbol('user')

interface UserPayload {
	username: string
	userID: number | null
	color1: number | null
	color2: number | null

	[x: string]: unknown
}

// eslint-disable-next-line @typescript-eslint/no-unsafe-declaration-merging, @typescript-eslint/no-empty-object-type
interface User extends UserPayload {}

/**
 * Represents a user.
 */
// eslint-disable-next-line @typescript-eslint/no-unsafe-declaration-merging
class User {
	[u]: UserPayload

	constructor(user: UserPayload) {
		this[u] = Helper.deepFreeze(user)

		Object.getOwnPropertyNames(this[u]).forEach((key) => {
			if (this[key] != null) {
				throw new Error('Attempting to override existing property')
			}

			Object.defineProperty(this, key, {
				value: this[u][key],
				enumerable: true,
			})
		})
	}

	get coloredUsername(): string {
		// No color
		if (this.color1 === null && this.color2 === null) {
			return this.username
		}

		// If color1 was null, color2 must be set, otherwise both would have been null
		let color1 = this.color1
		if (color1 === null) {
			color1 = this.color2!
		}

		// If color2 was null, color1 must be set, otherwise both would have been null
		let color2 = this.color2
		if (color2 === null) {
			color2 = this.color1!
		}

		// Single color, if both are identical, neither can be null
		if (color1 === color2) {
			return `<span style="color: ${Helper.intToRGBHex(
				color1
			)};">${StringUtil.escapeHTML(this.username)}</span>`
		}

		// Gradient
		const r1 = (color1 >> 16) & 0xff
		const r2 = (color2 >> 16) & 0xff
		const g1 = (color1 >> 8) & 0xff
		const g2 = (color2 >> 8) & 0xff
		const b1 = color1 & 0xff
		const b2 = color2 & 0xff

		const steps = this.username.length - 1
		const r = (r1 - r2) / steps
		const g = (g1 - g2) / steps
		const b = (b1 - b2) / steps

		return this[u].username
			.split('')
			.map((letter: string, index: number) => {
				const R = Math.round(r1 - index * r)
				const G = Math.round(g1 - index * g)
				const B = Math.round(b1 - index * b)

				return `<span style="color: rgb(${R}, ${G}, ${B})">${StringUtil.escapeHTML(
					letter
				)}</span>`
			})
			.join('')
	}

	get self(): boolean {
		return this.userID === CoreUser.userId
	}

	static getGuest(username: string): User {
		const payload = { username, userID: null, color1: null, color2: null }

		return new User(payload)
	}

	wrap(): { user: UserPayload } {
		return { user: this[u] }
	}

	toJSON(): UserPayload {
		return this[u]
	}
}

export = User
