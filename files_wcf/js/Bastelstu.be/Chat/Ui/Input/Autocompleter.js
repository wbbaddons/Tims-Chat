/*
 * Copyright (c) 2010-2021 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2026-03-04
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

define([
	'WoltLabSuite/Core/Dom/Util',
	'WoltLabSuite/Core/Event/Key',
	'WoltLabSuite/Core/Ui/Suggestion',
], function (DomUtil, EventKey, Suggestion) {
	'use strict'

	const DEPENDENCIES = ['UiInput']
	class Autocompleter extends Suggestion {
		constructor(input) {
			const elementId = DomUtil.identify(input.input)
			const options = {
				callbackSelect: (_elementId, selection) =>
					this.insertSelection(selection),
			}

			super(elementId, options)

			this.input = input
			this.completions = new Map()
			this.completionId = 0
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

		keyDown(...args) {
			return this._keyDown(...args)
		}

		_keyDown(event) {
			const result = (super.keyDown || super._keyDown).call(this, event)

			if (!result && EventKey.Enter(event)) {
				this.cancelNextSubmit = true
			}
		}

		keyUp(...args) {
			return this._keyUp(...args)
		}

		_keyUp(event) {
			const value = this.input.getText(true)

			if (this._value !== value) {
				this.sendCompletions([])
				this._value = value
			}
		}

		insertSelection(selection) {
			let text
			if ((text = this.completions.get(parseInt(selection.objectId, 10)))) {
				this.input.insertText(text, { append: false })
			}
		}

		sendCompletions(completions) {
			this.completions = new Map()

			const returnValues = completions.map((completion) => {
				this.completions.set(++this.completionId, completion)
				return { label: completion, objectID: this.completionId }
			})

			this._ajaxSuccess({ returnValues })
		}
	}
	Autocompleter.DEPENDENCIES = DEPENDENCIES

	return Autocompleter
})
