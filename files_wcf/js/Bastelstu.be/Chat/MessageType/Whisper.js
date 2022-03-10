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

define(['./Plain'], function (Plain) {
	'use strict'

	const DEPENDENCIES = ['UiInput'].concat(Plain.DEPENDENCIES || [])
	class Whisper extends Plain {
		constructor(input, ...superDeps) {
			super(...superDeps)

			this.input = input
		}

		render(message) {
			const fragment = super.render(message)

			if (this.input != null) {
				Array.prototype.forEach.call(
					fragment.querySelectorAll('[data-insert-whisper]'),
					function (el) {
						el.addEventListener(
							'click',
							function () {
								const username = el.dataset.insertWhisper
								const sanitizedUsername = username.replace(/"/g, '""')
								const command = `/whisper "${sanitizedUsername}"`

								if (this.input.getText().indexOf(command) !== 0) {
									this.input.insertText(`${command} `, {
										prepend: true,
										append: false,
									})
									this.input.focus()
								}
							}.bind(this)
						)
					}.bind(this)
				)
			}

			return fragment
		}

		joinable(a, b) {
			return (
				a.userID === b.userID && a.payload.recipient === b.payload.recipient
			)
		}
	}
	Whisper.DEPENDENCIES = DEPENDENCIES

	return Whisper
})
