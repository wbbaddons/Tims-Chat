/*
 * Copyright (c) 2010-2025 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2029-10-04
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

import parsec from 'Bastelstu.be/parser-combinator'
import type { IParser } from 'Bastelstu.be/parser-combinator'

const { C, F, N, X, Streams } = parsec
const response = parsec.parsec.response

/**
 * peek() transform a given parser into a new parser accepting
 * the same inputs, but without consuming the input stream.
 */
function peek<T>(p: IParser<T>): IParser<T> {
	return F.parse(function (input, index = 0) {
		return p.parse(input, index).fold(
			function (accept) {
				// Preserve the `accept.value`, but keep the original input, index
				// and indicate that nothing was consumed.
				return response.accept(accept.value, input, index, false)
			},
			function (reject) {
				return response.reject(input.location(reject.offset), false)
			}
		)
	})
}

const Whitespace: IParser<string> = F.satisfy<string>((item) => /\s/.test(item))

const Rest: IParser<string> = F.any.optrep().map((item) => item.join(''))
const Rest1: IParser<string> = F.any.rep().map((item) => item.join(''))

const AlnumTrigger: IParser<string> = C.letter
	.or(N.digit)
	.rep()
	.map((item) => item.join(''))
const SymbolicTrigger: IParser<string> = F.not(
	C.letter.or(N.digit).or(Whitespace)
)
	.rep()
	.map((item) => item.join(''))
const Slash: IParser<string> = C.char('/')
const Trigger: IParser<string | null> = Slash.thenRight(
	peek(Slash.map((_item) => null))
		.or(AlnumTrigger.thenLeft(Whitespace.rep().or(F.eos)))
		.or(SymbolicTrigger.thenLeft(Whitespace.optrep()))
).or(F.returns(null))
const Command: IParser<[string | null, string]> = Trigger.then(Rest)

const Quote: IParser<string> = C.char('"')
const QuotedUsername: IParser<string> = Quote.thenRight(
	Quote.thenRight(Quote).or(F.not(Quote)).rep()
)
	.thenLeft(Quote)
	.map((item) => item.join(''))
const Comma: IParser<string> = C.char(',')
const UnquotedUsername: IParser<string> = F.not(Comma.or(Quote).or(Whitespace))
	.then(
		F.not(Comma.or(Whitespace))
			.optrep()
			.map((item) => item.join(''))
	)
	.map((item) => item.join(''))
const Username: IParser<string> = QuotedUsername.or(UnquotedUsername)

const Decimal = (length: number): IParser<number> =>
	N.digit.occurrence(length).map((item) => parseInt(item.join(''), 10))

const Hexadecimal: IParser<string> = N.digit
	.or(C.charIn('abcdefABCDEF'))
	.rep()
	.map((x) => x.join(''))

const RGBHex: IParser<string> = C.char('#')
	.opt()
	.thenRight(
		Hexadecimal.filter((x) => x.length === 3 || x.length === 6).map((item) => {
			if (item.length === 3) {
				item = `${item[0]}${item[0]}${item[1]}${item[1]}${item[2]}${item[2]}`
			}

			return item
		})
	)
	.map((item) => `#${item}`)

const Dash: IParser<string> = C.char('-')
const Datestring: IParser<[number, number, number]> = Decimal(4)
	.filter((item) => 2000 <= item && item <= 2030)
	.thenLeft(Dash)
	.then(Decimal(2).filter((item) => 1 <= item && item <= 12))
	.thenLeft(Dash)
	.then(Decimal(2).filter((item) => 1 <= item))

const Colon: IParser<string> = C.char(':')
const Timestring: IParser<[number, number, number]> = Decimal(2)
	.filter((item) => 0 <= item && item <= 23)
	.thenLeft(Colon)
	.then(Decimal(2).filter((item) => 0 <= item && item <= 59))
	.thenLeft(Colon)
	.then(Decimal(2).filter((item) => 0 <= item && item <= 59))

const ISODate: IParser<Date> = Datestring.then(
	C.char('T').thenRight(Timestring).opt()
).map(function ([year, month, day, time]) {
	const date = new Date()
	date.setFullYear(year)
	date.setMonth(month - 1)
	date.setDate(day)

	time.map(function ([hour, minute, second]) {
		date.setHours(hour)
		date.setMinutes(minute)
		date.setSeconds(second)
	})

	return date
})

export = {
	Streams,
	stream: Streams,
	AlnumTrigger,
	Colon,
	Command,
	Dash,
	Datestring,
	Decimal,
	Hexadecimal,
	ISODate,
	Quote,
	QuotedUsername,
	RGBHex,
	Rest,
	Rest1,
	Slash,
	SymbolicTrigger,
	Timestring,
	Trigger,
	UnquotedUsername,
	Username,
	Whitespace,
	C,
	F,
	N,
	X,
}
