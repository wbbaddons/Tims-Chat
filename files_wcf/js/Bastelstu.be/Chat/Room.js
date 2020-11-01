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

define([ 'Bastelstu.be/PromiseWrap/Ajax'
       , 'WoltLabSuite/Core/Core'
       , './User'
       ], function (Ajax, Core, User) {
	"use strict";

	const DEPENDENCIES = [ 'sessionID', 'roomID' ]
	/**
	 * Represents a chat room.
	 */
	class Room {
		constructor(sessionID, roomID) {
			this.sessionID = sessionID
			this.roomID    = roomID
		}

		/**
		 * Sends a request to join the room.
		 *
		 * @returns {Promise}
		 */
		async join() {
			const payload = { className: 'chat\\data\\room\\RoomAction'
			                , actionName: 'join'
			                , parameters: { roomID: this.roomID
			                              , sessionID: this.sessionID
			                              }
			                }

			return Ajax.api(this, payload)
		}

		/**
		 * Sends a request to leave the room.
		 *
		 * @param {boolean} unload Send a beacon if true'ish and a regular AJAX request otherwise.
		 */
		leave(unload = false) {
			const payload = { className: 'chat\\data\\room\\RoomAction'
			                , actionName: 'leave'
			                , parameters: { roomID: this.roomID
			                              , sessionID: this.sessionID
			                              }
			                }

			if (unload && FormData && (navigator.sendBeacon || window.fetch)) {
				// Ordinary AJAX requests are unreliable during unload:
				// Use navigator.sendBeacon if available, otherwise hope
				// for the best and clean up based on a time out.

				const url = WSC_API_URL + 'index.php?ajax-proxy/&t=' + SECURITY_TOKEN

				const formData = new FormData()
				Core.serialize(payload)
				    .split('&')
				    .map((item) => item.split('='))
				    .map((item) => item.map(decodeURIComponent))
				    .forEach((item) => formData.append(item[0], item[1]))

				if (navigator.sendBeacon) {
					navigator.sendBeacon(url, formData)
				}

				if (window.fetch) {
					fetch(url, { method: 'POST', keepalive: true, redirect: 'follow', body: formData })
				}

				return Promise.resolve()
			}
			else {
				return Ajax.api(this, payload)
			}
		}

		/**
		 * Sends a request to retrieve the userIDs inhabiting this room.
		 *
		 * @returns {Promise}
		 */
		async getUsers() {
			const payload = { className: 'chat\\data\\room\\RoomAction'
			                , actionName: 'getUsers'
			                , objectIDs: [ this.roomID ]
			                }

			const result = await Ajax.api(this, payload)

			return Object.values(result.returnValues).map(user => new User(user))
		}

		_ajaxSetup() {
			return { silent: true
			       , ignoreError: true
			       }
		}
	}
	Room.DEPENDENCIES = DEPENDENCIES

	return Room
});
