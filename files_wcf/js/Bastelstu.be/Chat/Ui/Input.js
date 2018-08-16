/*
 * Copyright (c) 2010-2018 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2022-08-16
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

define([ '../console'
       , '../Helper'
       , 'WoltLabSuite/Core/Core'
       , 'WoltLabSuite/Core/Event/Key'
       , '../DataStructure/EventEmitter'
       , '../DataStructure/Throttle'
       ], function (console, Helper, Core, EventKey, EventEmitter, Throttle) {
	"use strict";

	class Input {
		constructor() {
			this.inputContainer = elById('chatInputContainer')
			this.input          = elBySel('textarea', this.inputContainer)
			this.charCounter    = elBySel('.charCounter', this.inputContainer)
			this.errorElement   = elBySel('.innerError', this.inputContainer)
		}

		bootstrap() {
			if (typeof window.elInnerError === 'function') {
				elRemove(this.errorElement)
			}

			this.input.addEventListener('keydown', this.handleInputKeyDown.bind(this))
			this.input.addEventListener('input',   Throttle(this.handleInput.bind(this)))

			Helper.makeFlexible(this.input)
			this.handleInput()
		}

		handleInput(event) {
			this.charCounter.textContent = `${this.input.value.length} / ${this.input.getAttribute('maxlength')}`
			this.emit('input')
		}

		handleInputKeyDown(event) {
			if (EventKey.Enter(event) && !event.shiftKey) {
				// prevent generation of a new line
				event.preventDefault()

				if (this.getText().length === 0) return

				const parameters = { cancel: false, input: this }
				this.emit('beforeSubmit', parameters)
				if (!parameters.cancel) {
					this.emit('submit')
				}
			}
			else if (EventKey.Tab(event)) {
				// prevent leaving the input
				event.preventDefault()

				this.emit('autocomplete')
			}
		}

		getText(raw = false) {
			if (raw) {
				return this.input.value
			}

			return this.input.value.trim()
		}

		select(start, end = undefined) {
			if (end === undefined) end = this.getText(true).length

			this.input.setSelectionRange(start, end)
		}

		focus() {
			this.input.focus()
		}

		insertText(text, options) {
			this.focus()

			options = Object.assign({ append:  true
			                        , prepend: false
			                        }, options)

			if (!(options.append || options.prepend)) {
				// replace
				this.input.value = text
			}

			if (options.append) {
				this.input.value += text;
			}

			if (options.prepend) {
				this.input.value = text + this.input.value;
			}

			// always position caret at the end
			const length = this.input.value.length
			this.input.setSelectionRange(length, length)

			Core.triggerEvent(this.input, 'input')
		}

		inputError(message) {
			if (typeof window.elInnerError === 'function') {
				elInnerError(this.inputContainer.firstElementChild, message)
			}
			else {
				this.inputContainer.classList.add('formError')
				this.errorElement.textContent = message
				elShow(this.errorElement)
			}
		}

		hideInputError() {
			if (typeof window.elInnerError === 'function') {
				elInnerError(this.inputContainer.firstElementChild, false)
			}
			else {
				this.inputContainer.classList.remove('formError')
				this.errorElement.textContent = ''
				elHide(this.errorElement)
			}
		}
	}
	EventEmitter(Input.prototype)

	return Input
});
