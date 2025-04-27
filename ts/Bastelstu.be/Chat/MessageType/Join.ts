/*
 * Copyright (c) 2010-2025 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2029-04-27
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

import MessageType from '../MessageType'
import * as Language from 'WoltLabSuite/Core/Language'
import type Message from '../Message'

class Join extends MessageType {
	shouldUpdateUserList(_message: Message): boolean {
		return true
	}

	renderPlainText(message: Message): string {
		return (
			'[➡️] ' +
			Language.get(
				'chat.messageType.be.bastelstu.chat.messageType.join.plain',
				{
					author: { username: message.username },
				}
			)
		)
	}
}

export = Join
