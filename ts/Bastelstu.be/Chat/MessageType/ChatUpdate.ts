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
import type Message from '../Message'

class ChatUpdate extends MessageType {
	preRender(_message: Message): void {
		// TODO: hcf()?
	}

	render(_message: Message): DocumentFragment | false {
		return false
	}
}

export = ChatUpdate
