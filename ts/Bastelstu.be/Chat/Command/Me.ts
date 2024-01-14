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

import Plain from './Plain'
import Parser from '../Parser'
import type { IParser } from 'Bastelstu.be/parser-combinator'

interface Payload {
	text: string
}

class Me extends Plain {
	getParameterParser(): IParser<Payload> {
		return Parser.Rest1.map((text) => ({ text }))
	}
}

export = Me
