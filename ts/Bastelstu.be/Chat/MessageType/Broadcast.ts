/*
 * Copyright (c) 2010-2026 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2030-08-27
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

import Plain from './Plain'

type PlainMessage = Parameters<Plain['renderPlainText']>[0]

class Broadcast extends Plain {
	renderPlainText(message: PlainMessage): string {
		return `[📢] ${message.payload.plaintextMessage}`
	}
}

export = Broadcast
