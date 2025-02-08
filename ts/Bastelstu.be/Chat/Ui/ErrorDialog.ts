/*
 * Copyright (c) 2010-2025 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2029-02-08
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

import * as Language from 'WoltLabSuite/Core/Language'
import Template from 'WoltLabSuite/Core/Template'
import * as UiDialog from 'WoltLabSuite/Core/Ui/Dialog'

const html = [
	'[type="x-text/template"]',
	'[data-application="be.bastelstu.chat"]',
	'[data-template-name="be-bastelstu-chat-errorDialog"]',
].join('')

const wrapper = new Template(document.querySelector(html)!.textContent!)

class ErrorDialog {
	constructor(message: string) {
		const options = {
			title: Language.get('wcf.global.error.title'),
			closable: false,
		}

		UiDialog.openStatic('chatError', wrapper.fetch({ message }), options)
	}
}

export = ErrorDialog
