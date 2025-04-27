/*
 * Copyright (c) 2010-2025 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2029-04-27
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

import console from './console'
import Push from 'Bastelstu.be/_Push'
import DomChangeListener from 'WoltLabSuite/Core/Dom/Change/Listener'
import DomUtil from 'WoltLabSuite/Core/Dom/Util'
import RepeatingTimer from 'WoltLabSuite/Core/Timer/Repeating'
import Ajax from 'Bastelstu.be/PromiseWrap/Ajax'

import type {
	DatabaseObjectActionPayload,
	DatabaseObjectActionResponse,
	RequestOptions,
} from 'WoltLabSuite/Core/Ajax/Data'

const timer = new RepeatingTimer(() => {
	BoxRoomList.updateBoxes()
}, 60e3)
const mapping = new Map<HTMLElement, BoxRoomList>()

interface RoomActionGetBoxRoomListPayload extends DatabaseObjectActionPayload {
	parameters: {
		isSidebar: boolean | string
		skipEmptyRooms: boolean | string
		activeRoomID: number | string
		boxID: number | string
	}
}

interface RoomActionGetBoxRoomListResponse
	extends DatabaseObjectActionResponse {
	returnValues: {
		// TODO: This value should always be set
		template?: string
	}
}

class BoxRoomList {
	container: HTMLElement

	constructor(container: HTMLElement) {
		this.container = container

		mapping.set(container, this)

		Push.onConnect(() => {
			timer.setDelta(300e3)
		}).catch((error) => {
			console.debug('BoxRoomList.Push.onConnect', error)
		})
		Push.onDisconnect(() => {
			timer.setDelta(60e3)
		}).catch((error) => {
			console.debug('BoxRoomList.Push.onDisconnect', error)
		})
		Push.onMessage('be.bastelstu.chat.join', () => {
			BoxRoomList.updateBoxes()
		}).catch((error) => {
			console.debug('BoxRoomList.Push.onMessage.Join', error)
		})
		Push.onMessage('be.bastelstu.chat.leave', () => {
			BoxRoomList.updateBoxes()
		}).catch((error) => {
			console.debug('BoxRoomList.Push.onMessage.Leave', error)
		})
	}

	static updateBoxes() {
		mapping.forEach((object) => {
			void object.update()
		})
	}

	async update() {
		const payload: RoomActionGetBoxRoomListPayload = {
			className: 'chat\\data\\room\\RoomAction',
			actionName: 'getBoxRoomList',
			parameters: {
				isSidebar: this.container.dataset.isSidebar!,
				skipEmptyRooms: this.container.dataset.skipEmptyRooms!,
				activeRoomID: this.container.dataset.activeRoomId!,
				boxID: this.container.dataset.boxId!,
			},
		}

		this.replace(
			(await Ajax.api(this, payload)) as RoomActionGetBoxRoomListResponse
		)
	}

	replace(data: RoomActionGetBoxRoomListResponse) {
		if (data.returnValues.template == null) {
			throw new Error('template could not be found in returnValues')
		}

		const fragment = DomUtil.createFragmentFromHtml(data.returnValues.template)
		const oldRoomList =
			this.container.querySelector<HTMLElement>('.chatBoxRoomList')
		const newRoomList = fragment.querySelector<HTMLElement>('.chatBoxRoomList')

		if (oldRoomList == null) {
			throw new Error('.chatBoxRoomList could not be found in container')
		}
		if (newRoomList == null) {
			throw new Error(
				'.chatBoxRoomList could not be found in returned template'
			)
		}

		if (oldRoomList.dataset.hash !== newRoomList.dataset.hash) {
			this.container.replaceChild(newRoomList, oldRoomList)
			DomChangeListener.trigger()
		}
	}

	_ajaxSetup(): RequestOptions {
		return { silent: true, ignoreError: true }
	}
}

export = BoxRoomList
