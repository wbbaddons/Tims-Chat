/*
 * Copyright (c) 2010-2025 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2029-10-04
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

import Ajax from 'Bastelstu.be/PromiseWrap/Ajax'
import * as Core from 'WoltLabSuite/Core/Core'

import User from './User'

import type { DatabaseObjectActionResponse, RequestOptions } from 'WoltLabSuite/Core/Ajax/Data'

interface RoomActionGetUsersResponse extends DatabaseObjectActionResponse {
	returnValues: ReturnType<User['toJSON']>[]
}

const DEPENDENCIES = ['sessionID', 'roomID']
/**
 * Represents a chat room.
 */
class Room {
	public static readonly DEPENDENCIES = DEPENDENCIES

	sessionID: string
	roomID: number

	constructor(sessionID: string, roomID: number) {
		this.sessionID = sessionID
		this.roomID = roomID
	}

	/**
	 * Sends a request to join the room.
	 */
	async join(): Promise<unknown> {
		const payload = {
			className: 'chat\\data\\room\\RoomAction',
			actionName: 'join',
			parameters: { roomID: this.roomID, sessionID: this.sessionID },
		}

		return Ajax.api(this, payload)
	}

	/**
	 * Sends a request to leave the room.
	 *
	 * @param unload Send a beacon if true'ish and a regular AJAX request otherwise.
	 */
	leave(unload = false): Promise<unknown> {
		const payload = {
			className: 'chat\\data\\room\\RoomAction',
			actionName: 'leave',
			parameters: { roomID: this.roomID, sessionID: this.sessionID },
		}

		if (unload) {
			const url =
				window.WSC_API_URL + 'index.php?ajax-proxy/&t=' + Core.getXsrfToken()

			const formData = new FormData()
			Core.serialize(payload)
				.split('&')
				.map((item) => item.split('='))
				.map((item) => item.map(decodeURIComponent))
				.forEach((item) => {
					formData.append(item[0], item[1])
				})

			// Ordinary AJAX requests are unreliable during unload.
			// Use navigator.sendBeacon instead and hope for the best,
			// otherwise we clean up based on a time out.
			navigator.sendBeacon(url, formData)

			return Promise.resolve()
		} else {
			return Ajax.api(this, payload)
		}
	}

	/**
	 * Sends a request to retrieve the userIDs inhabiting this room.
	 */
	async getUsers(): Promise<User[]> {
		const payload = {
			className: 'chat\\data\\room\\RoomAction',
			actionName: 'getUsers',
			objectIDs: [this.roomID],
		}

		const result = (await Ajax.api(this, payload)) as RoomActionGetUsersResponse

		return Object.values(result.returnValues).map((user) => new User(user))
	}

	_ajaxSetup(): RequestOptions {
		return { silent: true, ignoreError: true }
	}
}

export = Room
