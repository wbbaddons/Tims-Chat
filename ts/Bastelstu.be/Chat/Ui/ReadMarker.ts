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

import type MessageStream from './MessageStream'

const DEPENDENCIES = ['UiMessageStream']
class ReadMarker {
	public static readonly DEPENDENCIES = DEPENDENCIES

	messageStream: MessageStream

	constructor(messageStream: MessageStream) {
		this.messageStream = messageStream
	}

	bootstrap(): void {
		document.addEventListener('visibilitychange', () => {
			this.onVisibilitychange()
		})
	}

	onVisibilitychange(): void {
		if (document.hidden) {
			const ul = this.messageStream.stream.querySelector('ul')!
			const lc = ul.lastElementChild

			// delete previous markers
			Array.prototype.forEach.call(
				document.querySelectorAll('.readMarker'),
				(marker: Element) => {
					marker.classList.remove('readMarker')
				}
			)

			if (lc) {
				lc.classList.add('readMarker')
			}
		}
	}
}

export = ReadMarker
