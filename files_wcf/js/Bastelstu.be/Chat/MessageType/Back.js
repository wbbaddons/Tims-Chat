/*
 * Copyright (c) 2010-2018 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2024-11-01
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

define([ '../MessageType' ], function (MessageType) {
	"use strict";

	const DEPENDENCIES = [ 'ProfileStore', 'roomID' ].concat(MessageType.DEPENDENCIES || [ ])
	class Back extends MessageType {
		constructor(profileStore, roomID, ...superDeps) {
			super(...superDeps)

			this.profileStore = profileStore
			this.roomID = roomID
		}

		render(message) {
			const isSilent = message.payload.rooms.find(room => room.roomID === this.roomID).isSilent

			if (!isSilent) {
				return super.render(message)
			}
			else {
				return false
			}
		}

		shouldUpdateUserList(message) {
			return true
		}

		preProcess(message) {
			this.profileStore.expire(message.userID)
		}
	}
	Back.DEPENDENCIES = DEPENDENCIES

	return Back
});
