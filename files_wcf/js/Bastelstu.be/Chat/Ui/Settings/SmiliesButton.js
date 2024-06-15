/*
 * Copyright (c) 2010-2024 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2028-06-15
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

define(['./ToggleButton', 'WoltLabSuite/Core/Ui/Screen'], function (
	ToggleButton,
	UiScreen
) {
	'use strict'

	const DEPENDENCIES = ['UiInput'].concat(ToggleButton.DEPENDENCIES || [])
	class SmiliesButton extends ToggleButton {
		constructor(element, input, ...superDeps) {
			super(element, false, undefined, ...superDeps)

			this.input = input
		}

		bootstrap() {
			this.container = elById('smileyPickerContainer')

			// Remove this button if smileys are disabled
			if (!this.container) {
				elRemove(this.element.closest('li'))
			}

			this.closeButton = elById('smileyPickerCloseButton')

			// Initialize the smiley picker tab menu
			$('.messageTabMenu').messageTabMenu()

			$('#smilies-text').on(
				'mousedown',
				'.jsSmiley',
				this.insertSmiley.bind(this)
			)
			this.closeButton.addEventListener('mousedown', this.disable.bind(this))

			// Start in desktop mode
			this.mobile = false

			// Do not persist the state
			super.bootstrap()

			// Setup media queries
			UiScreen.on('screen-md-down', {
				match: this.enableMobile.bind(this),
				unmatch: this.disableMobile.bind(this),
				setup: this.setupMobile.bind(this),
			})
		}

		/**
		 * Initializes and enables the mobile smiley picker UI components.
		 *
		 * A second button mirroring this button’s click handler is
		 * inserted next to the message input while this button will
		 * be hidden.
		 */
		setupMobile() {
			this.shadowToggleButton = document.createElement('span')
			this.shadowToggleButton.classList.add(
				'smiliesToggleMobileButton',
				'button',
				'small'
			)
			this.shadowToggleButton.innerHTML =
				'<fa-icon size="24" name="face-smile"></fa-icon>'
			this.shadowToggleButton.addEventListener(
				'mousedown',
				this.onClick.bind(this)
			)

			const shadowContainer = elBySel('#chatInputContainer > div')
			shadowContainer.insertBefore(
				this.shadowToggleButton,
				shadowContainer.firstChild
			)

			this.enableMobile()
		}

		/**
		 * Enables the mobile smiley picker components.
		 *
		 * Hides this button and shows it’s mirror next to the message input.
		 */
		enableMobile() {
			this.mobile = true

			elHide(this.element.parentElement)
			elShow(this.shadowToggleButton)

			// Do not show the overlay when the viewport changes
			// and becomes smaller
			this.disable()
		}

		/**
		 * Disables the mobile smiley picker components.
		 *
		 * Shows this button and hides it’s mirror next to the message input.
		 * Also re-enables scrolling of the main body.
		 */
		disableMobile() {
			this.mobile = false

			elShow(this.element.parentElement)
			elHide(this.shadowToggleButton)

			UiScreen.scrollEnable()
		}

		/**
		 * Event handler to handle the insertion of smilies into the message input.
		 * This handler closes the fulls creen overlay of the mobile view after insertion.
		 *
		 * @param  {Event} event The event bound in the init() function
		 */
		insertSmiley(event) {
			event.preventDefault()
			event.stopPropagation()

			const smileyCode = event.currentTarget.children[0].getAttribute('alt')

			this.input.insertText(` ${smileyCode} `)

			if (this.mobile) {
				this.disable()
			}
		}

		/**
		 * Enables the smiley picker.
		 * If the mobile view is active, scrolling of the main body will be disabled.
		 */
		enable() {
			super.enable()

			elShow(this.container)
			elData(this.container, 'show', 'true')

			if (this.mobile) {
				UiScreen.scrollDisable()
			}
		}

		/**
		 * Disables the smiley picker.
		 * If the mobile view is active, scrolling of the main body will be re-enabled.
		 */
		disable() {
			super.disable()

			elHide(this.container)
			elData(this.container, 'show', 'false')

			if (this.mobile) {
				UiScreen.scrollEnable()
			}
		}
	}
	SmiliesButton.DEPENDENCIES = DEPENDENCIES

	return SmiliesButton
})
