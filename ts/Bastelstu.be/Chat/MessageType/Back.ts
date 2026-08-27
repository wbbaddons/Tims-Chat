/*
 * Copyright (c) 2010-2026 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2030-08-27
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

import MessageType from '../MessageType'

import type Message from '../Message'
import type ProfileStore from '../ProfileStore'

interface BackMessage extends Message {
	payload: {
		rooms: {
			roomID: number
			isSilent: boolean
		}[]
	}
}

const DEPENDENCIES = ['ProfileStore', 'roomID'].concat(MessageType.DEPENDENCIES)
class Back extends MessageType {
	public static readonly DEPENDENCIES = DEPENDENCIES

	readonly roomID: number

	constructor(
		profileStore: ProfileStore,
		roomID: number,
		...superDeps: ConstructorParameters<typeof MessageType>
	) {
		super(...superDeps)

		this.profileStore = profileStore
		this.roomID = roomID
	}

	render(message: BackMessage): DocumentFragment | false {
		const room = message.payload.rooms.find(
			(room) => room.roomID === this.roomID
		)

		if (room != null && !room.isSilent) {
			return super.render(message)
		} else {
			return false
		}
	}

	shouldUpdateUserList(_message: Message): boolean {
		return true
	}

	preProcess(message: Message): void {
		this.profileStore.expire(message.userID)
	}
}

export = Back
