/*
 * Copyright (c) 2010-2018 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2024-10-20
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

define([ 'WoltLabSuite/Core/Dom/Util'
       , 'WoltLabSuite/Core/Event/Key'
       , 'WoltLabSuite/Core/Ui/Suggestion'
       ], function (DomUtil, EventKey, Suggestion) {
	"use strict";

	const DEPENDENCIES = [ 'UiInput' ]
	class Autocompleter extends Suggestion {
		constructor(input) {
			const elementId = DomUtil.identify(input.input)
			const options = { callbackSelect: (() => null) }

			super(elementId, options)

			this.input = input
			this._options.callbackSelect = this.callbackSelect.bind(this)
		}

		bootstrap() {
			this.input.on('beforeSubmit', (event) => {
				if (event.target !== this.input) return

				if (this.isActive() || this.cancelNextSubmit) {
					event.detail.cancel = true
				}
				this.cancelNextSubmit = false
			})
		}

		_keyDown(event) {
			const result = super._keyDown(event)

			if (!result && EventKey.Enter(event)) {
				this.cancelNextSubmit = true
			}
		}

		_keyUp(event) {
			const value = this.input.getText(true)

			if (this._value !== value) {
				this._ajaxSuccess({ returnValues: [] })
				this._value = value
			}
		}

		callbackSelect(_, selected) {
			this.input.insertText(selected.objectId, { append: false })
		}

		_ajaxSuccess(...args) {
			this._value = this.input.getText(true)
			return super._ajaxSuccess(...args)
		}
	}
	Autocompleter.DEPENDENCIES = DEPENDENCIES

	return Autocompleter
});
