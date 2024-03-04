/*
 * Copyright (c) 2010-2024 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2028-03-14
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

define([
	'./console',
	'Bastelstu.be/_Push',
	'WoltLabSuite/Core/Dom/Change/Listener',
	'WoltLabSuite/Core/Dom/Util',
	'WoltLabSuite/Core/Timer/Repeating',
	'Bastelstu.be/PromiseWrap/Ajax',
], function (console, Push, DomChangeListener, DomUtil, RepeatingTimer, Ajax) {
	'use strict'

	let timer = undefined
	const mapping = new Map()

	class BoxRoomList {
		constructor(container) {
			this.container = container

			mapping.set(container, this)

			if (timer == null) {
				timer = new RepeatingTimer(
					BoxRoomList.updateBoxes.bind(BoxRoomList),
					60e3
				)
			}

			Push.onConnect(timer.setDelta.bind(timer, 300e3)).catch((error) => {
				console.debug(error)
			})
			Push.onDisconnect(timer.setDelta.bind(timer, 60e3)).catch((error) => {
				console.debug(error)
			})
			Push.onMessage(
				'be.bastelstu.chat.join',
				BoxRoomList.updateBoxes.bind(BoxRoomList)
			).catch((error) => {
				console.debug(error)
			})
			Push.onMessage(
				'be.bastelstu.chat.leave',
				BoxRoomList.updateBoxes.bind(BoxRoomList)
			).catch((error) => {
				console.debug(error)
			})
		}

		static updateBoxes() {
			mapping.forEach((object) => {
				object.update()
			})
		}

		async update() {
			const payload = {
				className: 'chat\\data\\room\\RoomAction',
				actionName: 'getBoxRoomList',
				parameters: {},
			}

			payload.parameters.activeRoomID = this.container.dataset.activeRoomId
			payload.parameters.boxID = this.container.dataset.boxId
			payload.parameters.isSidebar = this.container.dataset.isSidebar
			payload.parameters.skipEmptyRooms = this.container.dataset.skipEmptyRooms

			this.replace(await Ajax.api(this, payload))
		}

		replace(data) {
			if (data.returnValues.template == null)
				throw new Error('template could not be found in returnValues')

			const fragment = DomUtil.createFragmentFromHtml(
				data.returnValues.template
			)
			const oldRoomList = this.container.querySelector('.chatBoxRoomList')
			const newRoomList = fragment.querySelector('.chatBoxRoomList')

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

		_ajaxSetup() {
			return { silent: true, ignoreError: true }
		}
	}

	return BoxRoomList
})
