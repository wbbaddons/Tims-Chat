/*
 * Copyright (c) 2010-2024 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2028-01-13
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

define(['WoltLabSuite/Core/Language', '../MessageType'], function (
	Language,
	MessageType
) {
	'use strict'

	class Where extends MessageType {
		render(message) {
			const fragment = super.render(message)

			const button = document.createElement('button')
			button.classList.add('button', 'small', 'jsTooltip', 'hideIcon')
			button.setAttribute('title', Language.get('wcf.global.button.hide'))

			button.addEventListener('click', () => {
				button.closest('.chatMessageBoundary').hidden = true
			})

			const icon = document.createElement('span')
			icon.classList.add('icon', 'icon16', 'fa-times')

			button.append(icon)

			const elem = fragment.querySelector('.jsRoomInfo > .containerHeadline')
			elem.insertBefore(button, elem.firstChild)

			return fragment
		}
	}

	return Where
})
