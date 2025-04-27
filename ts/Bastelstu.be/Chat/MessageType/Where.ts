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

import * as Language from 'WoltLabSuite/Core/Language'
import MessageType from '../MessageType'

import type Message from '../Message'

interface WhereMessage extends Message {
	payload: {
		roomID: number
		users: {
			userID: number
			username: string
			link: string
		}[]
		room: {
			roomID: number
			title: string
			link: string
		}
	}[]
}

class Where extends MessageType {
	render(message: Message): DocumentFragment | false {
		const fragment = super.render(message)

		if (fragment === false) {
			return false
		}

		const button = document.createElement('button')
		button.classList.add('button', 'small', 'jsTooltip', 'hideIcon')
		button.setAttribute('title', Language.get('wcf.global.button.hide'))

		button.addEventListener('click', () => {
			button.closest<HTMLElement>('.chatMessageBoundary')!.hidden = true
		})

		const icon = document.createElement('fa-icon')
		icon.setIcon('xmark')

		button.append(icon)

		const elem = fragment.querySelector('.jsRoomInfo > .containerHeadline')!
		elem.insertBefore(button, elem.firstChild)

		return fragment
	}
}

export = Where
