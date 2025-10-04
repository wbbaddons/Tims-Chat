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

import ToggleButton from './ToggleButton'
import type MessageStream from '../MessageStream'

const DEPENDENCIES = ['UiMessageStream']
class AutoscrollButton extends ToggleButton {
	public static readonly DEPENDENCIES = DEPENDENCIES

	messageStream: MessageStream

	constructor(element: Element, messageStream: MessageStream) {
		super(element, true, undefined)

		this.messageStream = messageStream

		this.messageStream.on('reachedBottom', () => {
			this.enable()
		})
		this.messageStream.on('scrollUp', () => {
			this.disable()
		})
	}

	enable() {
		super.enable()

		this.messageStream.enableAutoscroll = true
	}

	disable() {
		super.disable()

		this.messageStream.enableAutoscroll = false
	}
}

export = AutoscrollButton
