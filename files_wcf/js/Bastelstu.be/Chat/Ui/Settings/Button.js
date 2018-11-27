/*
 * Copyright (c) 2010-2018 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2022-11-27
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

define([ ], function () {
	'use strict';

	const DEPENDENCIES = [ ]
	class Button {
		constructor(element) {
			if (!element || !element instanceof Element) throw new Error('No DOM element provided')

			this.element = element
		}

		bootstrap() {
			this.element.addEventListener('click', this.onClick.bind(this))
		}

		onClick(event) {
			event.preventDefault()
		}
	}
	Button.DEPENDENCIES = DEPENDENCIES

	return Button
});
