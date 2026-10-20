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

import Helper from './Helper'
import * as DateUtil from 'WoltLabSuite/Core/Date/Util'
import User from 'WoltLabSuite/Core/User'

import type MessageType from './MessageType'

interface ViewableMessagePayload {
	messageID: number
	userID: number
	username: string
	time: number
	payload: unknown
	objectType: string
	link: string
	isIgnored: boolean
	isDeleted: boolean
}

// eslint-disable-next-line @typescript-eslint/no-unsafe-declaration-merging, @typescript-eslint/no-empty-object-type
interface Message extends ViewableMessagePayload {}

// eslint-disable-next-line @typescript-eslint/no-unsafe-declaration-merging
class Message {
	#m: ViewableMessagePayload
	MessageType: Record<string, MessageType>

	constructor(
		MessageType: Message['MessageType'],
		message: ViewableMessagePayload
	) {
		this.#m = Helper.deepFreeze(message)
		this.MessageType = MessageType
	}

	get messageID(): number {
		return this.#m.messageID
	}

	get objectType(): string {
		return this.#m.objectType
	}

	getMessageType(): MessageType {
		return this.MessageType[this.objectType.replace(/\./g, '-')]
	}

	get time(): number {
		return this.#m.time
	}

	get formattedTime(): string {
		return DateUtil.format(this.date, 'H:i:s')
	}

	get date(): Date {
		return new Date(this.#m.time * 1000)
	}

	get link(): string {
		return this.#m.link
	}

	get userID(): number {
		return this.#m.userID
	}

	get username(): string {
		return this.#m.username
	}

	get isIgnored(): boolean {
		return this.#m.isIgnored
	}

	get isDeleted(): boolean {
		return this.#m.isDeleted
	}

	get payload(): unknown {
		return this.#m.payload
	}

	isOwnMessage(): boolean {
		return this.userID === User.userId
	}

	wrap(): { message: ViewableMessagePayload } {
		return { message: this.#m }
	}

	toJSON(): ViewableMessagePayload {
		return this.#m
	}
}

export = Message
