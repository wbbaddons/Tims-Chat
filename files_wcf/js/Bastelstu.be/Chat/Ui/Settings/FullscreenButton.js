/*
 * Copyright (c) 2010-2021 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2026-03-10
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

define(['./ToggleButton'], function (ToggleButton) {
	'use strict'

	class FullscreenButton extends ToggleButton {
		constructor(element, ...superDeps) {
			super(
				element,
				false,
				'Bastelstu.be/Chat/Ui/Settings/FullscreenButton',
				...superDeps
			)
		}

		enable() {
			super.enable()
			document.querySelector('html').classList.add('fullscreen')
		}

		disable() {
			super.disable()
			document.querySelector('html').classList.remove('fullscreen')
		}
	}

	return FullscreenButton
})
