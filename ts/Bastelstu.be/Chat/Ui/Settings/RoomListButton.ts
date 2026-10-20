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

import Helper from '../../Helper'
import Button from './Button'
import DomUtil from 'WoltLabSuite/Core/Dom/Util'
import * as Language from 'WoltLabSuite/Core/Language'
import * as UiScreen from 'WoltLabSuite/Core/Ui/Screen'

class RoomListButton extends Button {
	roomList: HTMLElement
	mobile: boolean
	open: boolean
	sidebar: HTMLElement
	closeButton: HTMLElement | undefined

	constructor(element: Element) {
		super(element)

		const roomList = document.querySelector<HTMLElement>(
			`[data-box-identifier='be.bastelstu.chat.roomListSidebar']`
		)
		if (!roomList) {
			element.remove()
			return
		}
		this.roomList = roomList

		this.mobile = false
		this.open = false
		this.sidebar = document.querySelector('.sidebar')!

		UiScreen.on('screen-xs', {
			match: () => {
				this.enableMobile()
			},
			unmatch: () => {
				this.disableMobile()
			},
			setup: () => {
				this.enableMobile()
			},
		})
	}

	enableMobile(): void {
		this.mobile = true
		this.element.parentElement!.hidden = false
	}

	disableMobile(): void {
		if (this.open) {
			this.show(false)
		}

		this.element.parentElement!.hidden = true
		this.mobile = false
	}

	show(doShow = true): void {
		if (doShow) {
			this.open = true
			this.sidebar.style.setProperty('display', 'contents', '')

			const siblings = Helper.getElementSiblings(this.roomList)
			for (const sibling of siblings) {
				DomUtil.hide(sibling as HTMLElement)
			}

			this.closeButton = document.createElement('span')
			this.closeButton.classList.add('modalCloseButton')
			this.closeButton.innerText = Language.get('wcf.global.button.close')
			this.closeButton.addEventListener('click', () => {
				this.show(false)
			})
			this.roomList.appendChild(this.closeButton)

			this.roomList.dataset.show = 'true'

			if (this.mobile) {
				UiScreen.scrollDisable()
			}
		} else {
			delete this.roomList.dataset.show
			this.closeButton?.remove()

			const siblings = Helper.getElementSiblings(this.roomList)
			for (const sibling of siblings) {
				DomUtil.show(sibling as HTMLElement)
			}

			this.sidebar.style.removeProperty('display')
			this.open = false

			if (this.mobile) {
				UiScreen.scrollEnable()
			}
		}
	}

	onClick(event: MouseEvent): void {
		super.onClick(event)

		this.show(!this.open)
	}
}

export = RoomListButton
