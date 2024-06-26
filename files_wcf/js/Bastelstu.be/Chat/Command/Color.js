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

define(['../Command', '../Parser'], function (Command, Parser) {
	'use strict'

	class Color extends Command {
		getParameterParser() {
			// Either match a color in hexadecimal RGB notation or a color name (just letters)
			const color = Parser.F.try(
				Parser.RGBHex.map((color) => ({
					type: 'hex',
					value: color.replace(/^#/, ''),
				}))
			).or(new Parser.X().word().map((word) => ({ type: 'word', value: word })))

			// Either match a single color or two colors separated by a space
			return Parser.F.try(color.then(Parser.C.char(' ').thenRight(color))).or(
				color.map((item) => [item])
			)
		}
	}

	return Color
})
