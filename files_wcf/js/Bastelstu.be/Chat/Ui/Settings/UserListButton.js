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
	'../../Helper',
	'./Button',
	'WoltLabSuite/Core/Dom/Util',
	'WoltLabSuite/Core/Language',
	'WoltLabSuite/Core/Ui/Screen'
], function (
	Helper,
	Button,
	DomUtil,
	Language,
	UiScreen
) {
	'use strict'

	const DEPENDENCIES = [].concat(Button.DEPENDENCIES || [])
	class UserListButton extends Button {
		constructor(element, ...superDeps) {
			super(element, ...superDeps)

			this.mobile = false
			this.open = false
			this.userList = document.getElementById('chatUserList')
			this.sidebar = document.querySelector('.sidebar')

			UiScreen.on('screen-xs', {
				match: () => this.enableMobile(),
				unmatch: () => this.disableMobile(),
				setup: () => this.enableMobile(),
			})
		}

		enableMobile() {
			this.mobile = true
			this.element.parentElement.hidden = false
		}

		disableMobile() {
			if (this.open) {
				this.show(false)
			}

			this.element.parentElement.hidden = true
			this.mobile = false
		}

		show(doShow = true) {
			if (doShow) {
				this.open = true
				this.sidebar.style.setProperty('display', 'contents', '');

				for (let sibling of Helper.getElementSiblings(this.userList)) {
					DomUtil.hide(sibling)
				}

				this.closeButton = document.createElement('span')
				this.closeButton.classList.add('modalCloseButton')
				this.closeButton.innerText = Language.get('wcf.global.button.close')
				this.closeButton.addEventListener('click', () => this.show(false))
				this.userList.appendChild(this.closeButton)

				this.userList.dataset.show = 'true'

				if (this.mobile) {
					UiScreen.scrollDisable()
				}
			}
			else {
				delete this.userList.dataset.show
				this.closeButton.remove()

				for (let sibling of Helper.getElementSiblings(this.userList)) {
					DomUtil.show(sibling)
				}

				this.sidebar.style.removeProperty('display')
				this.open = false

				if (this.mobile) {
					UiScreen.scrollEnable()
				}
			}
		}

		async onClick(event) {
			super.onClick(event)

			this.show(!this.open)
		}
	}
	UserListButton.DEPENDENCIES = DEPENDENCIES

	return UserListButton
})
