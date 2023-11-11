/*
 * Copyright (c) 2010-2024 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2028-01-13
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

define([
	'WoltLabSuite/Core/Language',
	'WoltLabSuite/Core/Template',
	'WoltLabSuite/Core/Ui/Dialog',
], function (Language, Template, UiDialog) {
	'use strict'

	const html = [
		'[type="x-text/template"]',
		'[data-application="be.bastelstu.chat"]',
		'[data-template-name="be-bastelstu-chat-errorDialog"]',
	].join('')

	const wrapper = new Template(elBySel(html).textContent)

	class ErrorDialog {
		constructor(message) {
			const options = {
				title: Language.get('wcf.global.error.title'),
				closable: false,
			}

			UiDialog.openStatic('chatError', wrapper.fetch({ message }), options)
		}
	}

	return ErrorDialog
})
