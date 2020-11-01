/*
 * Copyright (c) 2010-2018 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2024-11-01
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

define([ 'Bastelstu.be/parser-combinator'
       ], function (parsec) {
	"use strict";

	const { C, F, N, X, parser, Streams } = parsec
	const response = parsec.parsec.response

	const peek = function (p) {
		return new parser((input, index = 0) =>
			p
			.parse(input, index)
			.fold(
				accept => response.accept(accept.value, accept.input, index, false),
				reject => response.reject(input.location(reject.offset), false)
			)
		);
	}

	const Whitespace = F.satisfy(item => /\s/.test(item))

	const Rest = F.any.optrep().map(item => item.join(''))
	const Rest1 = F.any.rep().map(item => item.join(''))

	const AlnumTrigger = C.letter.or(N.digit).rep().map(item => item.join(''))
	const SymbolicTrigger = F.not(C.letter.or(N.digit).or(Whitespace)).rep().map(item => item.join(''))
	const Slash = C.char('/')
	const Trigger = Slash.thenRight(
		peek(Slash.map(item => null)).or(AlnumTrigger.thenLeft(Whitespace.rep().or(F.eos))).or(SymbolicTrigger.thenLeft(Whitespace.optrep()))
	).or(F.returns(null))
	const Command = Trigger.then(Rest)

	const Quote = C.char('"')
	const QuotedUsername = Quote.thenRight(
		((Quote.thenRight(Quote)).or(F.not(Quote))).rep()
	).thenLeft(Quote).map(item => item.join(''))
	const Comma = C.char(',')
	const UnquotedUsername = F.not(Comma.or(Quote).or(Whitespace)).then(F.not(Comma.or(Whitespace)).optrep().map(item => item.join(''))).map(item => item.join(''))
	const Username = QuotedUsername.or(UnquotedUsername)

	const Decimal = (length) => N.digit.occurrence(length).map(item => parseInt(item.join(''), 10))

	const Hexadecimal = N.digit
		.or(C.charIn('abcdefABCDEF'))
		.rep()
		.map(x => x.join(''))

	const RGBHex = (C.char('#').opt())
		.thenRight(
			Hexadecimal.filter(x => x.length === 3 || x.length === 6)
			.map(item => {
				if (item.length === 3) {
					item = `${item[0]}${item[0]}${item[1]}${item[1]}${item[2]}${item[2]}`
				}

				return item
			})
		).map(item => `#${item}`)

	const Dash = C.char('-')
	const Datestring =   Decimal(4).filter(item => 2000 <= item && item <= 2030)
	.thenLeft(Dash).then(Decimal(2).filter(item => 1 <= item && item <= 12))
	.thenLeft(Dash).then(Decimal(2).filter(item => 1 <= item))

	const Colon = C.char(':')
	const Timestring =    Decimal(2).filter(item => 0 <= item && item <= 23)
	.thenLeft(Colon).then(Decimal(2).filter(item => 0 <= item && item <= 59))
	.thenLeft(Colon).then(Decimal(2).filter(item => 0 <= item && item <= 59))

	const ISODate = Datestring.then(C.char('T').thenRight(Timestring).opt()).map(function ([ year, month, day, time ]) {
		const date = new Date()
		date.setFullYear(year)
		date.setMonth(month - 1)
		date.setDate(day)

		time.map(function ([ hour, minute, second ]) {
			date.setHours(hour)
			date.setMinutes(minute)
			date.setSeconds(second)
		})

		return date
	})

	return {
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
});
