/*
 * Copyright (c) 2010-2018 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2024-11-20
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

define([], function () {
	'use strict'

	const DEPENDENCIES = ['UiMessageStream']
	class ReadMarker {
		constructor(messageStream) {
			this.messageStream = messageStream
		}

		bootstrap() {
			document.addEventListener(
				'visibilitychange',
				this.onVisibilitychange.bind(this)
			)
		}

		onVisibilitychange() {
			if (document.hidden) {
				const ul = elBySel('ul', this.messageStream.stream)
				let lc = ul.lastElementChild

				// delete previous markers
				Array.prototype.forEach.call(
					document.querySelectorAll('.readMarker'),
					(marker) => {
						marker.classList.remove('readMarker')
					}
				)

				if (lc) {
					lc.classList.add('readMarker')
				}
			}
		}
	}
	ReadMarker.DEPENDENCIES = DEPENDENCIES

	return ReadMarker
})
