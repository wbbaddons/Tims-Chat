/*
 * Copyright (c) 2010-2026 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2030-10-20
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

import { IParser } from 'Bastelstu.be/parser-combinator'
import Command from '../Command'
import Parser from '../Parser'

interface ColorParameter {
	type: 'hex' | 'word'
	value: string
}

type Payload = [ColorParameter] | [ColorParameter, ColorParameter]

class Color extends Command {
	getParameterParser(): IParser<Payload> {
		// Either match a color in hexadecimal RGB notation or a color name (just letters)
		const color: IParser<ColorParameter> = Parser.F.try(
			Parser.RGBHex.map(
				(color): ColorParameter => ({
					type: 'hex',
					value: color.replace(/^#/, ''),
				})
			)
		).or(
			new Parser.X()
				.word()
				.map((word): ColorParameter => ({ type: 'word', value: word }))
		)

		// Either match a single color or two colors separated by a space
		return Parser.F.try(color.then(Parser.C.char(' ').thenRight(color))).or(
			color.map((item) => [item])
		)
	}
}

export = Color
