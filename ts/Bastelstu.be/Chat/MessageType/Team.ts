/*
 * Copyright (c) 2010-2025 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2029-04-23
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

import Message from '../Message'
import Plain from './Plain'

type PlainMessage = Parameters<Plain['renderPlainText']>[0]

class Team extends Plain {
	joinable(a: Message, b: Message): boolean {
		return a.userID === b.userID
	}

	renderPlainText(message: PlainMessage): string {
		return `[⭐] ${message.payload.plaintextMessage}`
	}
}

export = Team
