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

define([
	'./Helper',
	'WoltLabSuite/Core/Date/Util',
	'WoltLabSuite/Core/User',
], function (Helper, DateUtil, User) {
	'use strict'

	const m = Symbol('message')

	class Message {
		constructor(MessageType, message) {
			this[m] = Helper.deepFreeze(message)
			this.MessageType = MessageType
		}

		get messageID() {
			return this[m].messageID
		}

		get objectType() {
			return this[m].objectType
		}

		getMessageType() {
			return this.MessageType[this.objectType.replace(/\./g, '-')]
		}

		get time() {
			return this[m].time
		}

		get formattedTime() {
			return DateUtil.format(this.date, 'H:i:s')
		}

		get date() {
			return new Date(this[m].time * 1000)
		}

		get link() {
			return this[m].link
		}

		get userID() {
			return this[m].userID
		}

		get username() {
			return this[m].username
		}

		get isIgnored() {
			return this[m].isIgnored
		}

		get isDeleted() {
			return this[m].isDeleted
		}

		get payload() {
			return this[m].payload
		}

		isOwnMessage() {
			return this.userID === User.userId
		}

		wrap() {
			return { message: this[m] }
		}

		toJSON() {
			return this[m]
		}
	}

	return Message
})
