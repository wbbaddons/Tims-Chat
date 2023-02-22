/*
 * Copyright (c) 2010-2021 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2027-02-22
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

define(['./ToggleButton'], function (ToggleButton) {
	'use strict'

	const DEPENDENCIES = ['UiMessageStream'].concat(
		ToggleButton.DEPENDENCIES || []
	)
	class AutoscrollButton extends ToggleButton {
		constructor(element, messageStream, ...superDeps) {
			super(element, true, undefined, ...superDeps)

			this.messageStream = messageStream

			this.messageStream.on('reachedBottom', this.enable.bind(this))
			this.messageStream.on('scrollUp', this.disable.bind(this))
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
	AutoscrollButton.DEPENDENCIES = DEPENDENCIES

	return AutoscrollButton
})
