/**
 * Type definitions for @masala/parser v0.6.1
 *
 * Partially inspired by the upstream definitions:
 * https://github.com/masala/masala-parser/blob/v0.6.1/masala-parser.d.ts
 */

/* eslint-disable @typescript-eslint/no-unsafe-declaration-merging */
/* eslint-disable @typescript-eslint/no-empty-interface */
/* eslint-disable @typescript-eslint/no-extraneous-class */
/* eslint-disable @typescript-eslint/no-explicit-any */

type Merge<T, U> = T extends [...rest: infer R] ? [...R, U] : [T, U]

type MasalaOptionEmpty = { __MASALA_EMPTY__: 'empty' }

export type None = Option<MasalaOptionEmpty>

export declare class Option<T = any> {
	value: T
	constructor(value: T)
	isPresent(): boolean
	map<X>(bindCall: (value: T) => X): Option<X> | this
	flatMap<X>(bindCall: (value: T) => X): X | this
	filter(f: (value: T) => boolean): this | None
	get(): T
	orElse<X>(v: X): T | X
	orLazyElse<X>(v: () => X): T | X
}

type Success<V> = Try<V, never>
type Failure<E> = Try<never, E>

declare class Try<V = any, E = any> {
	error: E
	value: V
	constructor(value: V, error: E)
	isSuccess(): boolean
	isFailure(): boolean
	onSuccess(f: (value: V) => void): this
	onFailure(f: (error: E) => void): this
	map<X, Y>(bindCall: (value: V) => X): Success<X> | Failure<Y> | this
	flatMap<X, Y>(bindCall: (value: V) => X): X | Failure<Y> | this
	success(): V
	failure(): E
	recoverWith<X>(otherValue: X): V | X
	lazyRecoverWith<X>(recoverFunction: (error?: E) => X): V | X
	filter(f: (value: V) => boolean): this | Failure<Error>
}

declare class Unit {}

declare class List<T> {
	value: T[]
	constructor(value: T[])
	size(): number
	isEmpty(): boolean
	add(element: T): List<T>
	append(list: List<T>): List<T>
	filter(funcall: (value: T) => boolean): List<T>
	map<X>(funcall: (value: T) => X): List<X>
	flatMap<X>(funcall: (value: T) => X): List<X>
	array(): T[]
	join(sep: string): string
}

declare const data: {
	option: {
		some: <T>(value: T) => Option<T>
		none: () => Option<undefined>
	}
	atry: {
		success: <V>(value: V) => Success<V>
		failure: <E>(error: E) => Failure<E>
	}
	unit: Unit
}

declare class Tokenizer {
	parser: Parser
	constructor(keywords: string)
	tokenize(charstream: Stream<string>): Try<Token>
}

declare class Genlex {
	identParser: Parser
	keywordParser: Parser
	constructor(keywords: string[])
	space(): Parser
	spaces(): Parser
	keyword(f: Parser): Parser
	ident(f: Parser): Parser
	number(f: Parser): Parser
	string(f: Parser): Parser
	char(f: Parser): Parser
	token(f: Parser): Parser
	tokenBetweenSpaces(f: Parser): Parser
	tokens(f: Parser): Parser
}

declare class Token {
	constructor()
	keyword(): Option
	ident(): Option
	number(): Option
	string(): Option
	char(): Option
}

declare class TKKeyword<T = any> extends Token {
	value: T
	constructor(value: T)
	keyword(): Option<T>
}

declare class TKIdent<T = any> extends Token {
	value: T
	constructor(value: T)
	ident(): Option<T>
}

declare class TKNumber<T = any> extends Token {
	value: T
	constructor(value: T)
	number(): Option<T>
}

declare class TKString<T = any> extends Token {
	value: T
	constructor(value: T)
	string(): Option<T>
}

declare class TKChar<T = any> extends Token {
	value: T
	constructor(value: T)
	char(): Option<T>
}

declare const genlex: {
	genlex: {
		factory: (
			keyword: Parser,
			ident: Parser,
			number: Parser,
			string: Parser,
			char: Parser,
		) => {
			keyword: Parser
			ident: Parser
			number: Parser
			string: Parser
			char: Parser
		}
		generator: (keywords: string[]) => Genlex
	}
	token: {
		builder: {
			keyword: <T>(value: T) => TKKeyword<T>
			ident: <T>(value: T) => TKIdent<T>
			number: <T>(value: T) => TKNumber<T>
			string: <T>(value: T) => TKString<T>
			char: <T>(value: T) => TKChar<T>
		}
		parser: {
			keyword: Parser
			ident: Parser
			number: Parser
			string: Parser
			char: Parser
		}
	}
	tokenizer: (keywords: string[]) => Tokenizer
}

export interface ListParser<T> extends IParser<List<T>> {
	parse<X>(stream: Stream<X>, index?: number): ListResponse<T>
}

export interface SingleParser<T> extends IParser<T> {
	parse<X>(stream: Stream<X>, index?: number): SingleResponse<T>
}

export interface VoidParser extends IParser<void> {
	parse<X>(stream: Stream<X>, index?: number): VoidResponse
}

export interface IParser<T> {
	new (parse: IParser<T>['parse']): IParser<T>

	parse<X>(stream: Stream<X>, index?: number): Response<T>

	map<Y>(f: (value: T) => Y): IParser<Y>

	flatMap<Y>(f: () => Y): IParser<Y>

	filter(predicate: (value: T) => boolean): this
	match(value: T): this

	then(p: VoidParser): IParser<T>
	then<Y>(p: IParser<Y>): IParser<Merge<T, Y>>

	thenLeft(p: IParser<any>): this

	thenRight<X, P extends IParser<X>>(p: P): P

	thenReturns<Y>(value: Y): SingleParser<Y>

	or<Y>(p: IParser<Y>): IParser<T | Y>

	concat: IParser<T>['then']
	drop(): VoidParser
	opt(): IParser<Option<T>>
	rep(): ListParser<T>
	occurrence(n: number): ListParser<T>
	optrep(): IParser<List<T>>
	chain<Y, P extends IParser<Y>>(highParser: P): P

	debug(hint?: string, details?: boolean): IParser<T>
}

interface Parser<T> extends IParser<T> {}

// eslint-disable-next-line @typescript-eslint/no-unused-vars
declare class Parser<T = any> {
	constructor(parse: IParser<T>['parse'])
}

declare function _string(s: string): Parser<string>

export interface ArrayResponse<T> extends Response<T> {
	value: T[]
}

export interface ListResponse<T> extends Response<List<T>> {
	value: List<T>
}

export interface OptionResponse<T> extends Response<T> {
	value: Option<T>
}

export interface SingleResponse<T> extends Response<T> {
	value: T
}

export interface VoidResponse extends Response<void> {
	// eslint-disable-next-line @typescript-eslint/no-invalid-void-type
	value: void
}

interface Response<T> {
	fold(accept: (v: Accept<T>) => Response<T>, reject?: (v: Reject) => Response<T>): Response<T>
}

// eslint-disable-next-line @typescript-eslint/no-unused-vars
declare class Response<T = any> {
	input: Stream<T>
	offset: number
	consumed: boolean

	isAccepted(): boolean
	toTry(): Try<T, Error>
	isConsumed(): boolean
}

declare class Reject<T = any> extends Response<T> {
	constructor(offset: number, consumed: boolean)

	fold(_: any, reject: (v: Reject<T>) => Response<T>): Response<T>
	map(): this
	flatMap(): this
	filter(): Reject<T>
}

declare class Accept<T = any> extends Response<T> {
	value: T
	constructor(value: T, input: Stream<T>, offset: number, consumed: boolean)

	isConsumed(): boolean
	fold(accept: (v: Accept<T>) => Response<T>): Response<T>
	map<S>(callback: (value: T) => S): Accept<S>
	flatMap<S>(callback: (value: T) => S): S
	filter(predicate: (value: T) => boolean): this | Reject
}

declare const UTF8_LETTER: unique symbol
declare const OCCIDENTAL_LETTER: unique symbol
declare const ASCII_LETTER: unique symbol

export declare const C: {
	utf8Letter: Parser<string>
	letter: Parser<string>
	letterAs: (symbol?: typeof UTF8_LETTER | typeof OCCIDENTAL_LETTER | typeof ASCII_LETTER) => Parser<string>
	letters: Parser<string>
	lettersAs: (
		symbolOrTestFunctionOrRegex: typeof UTF8_LETTER | typeof OCCIDENTAL_LETTER | typeof ASCII_LETTER,
	) => Parser<string>
	emoji: Parser<string>
	notChar: (c: string) => Parser<string>
	char: (c: string) => SingleParser<string>
	charIn: (c: string) => Parser<string>
	charNotIn: (c: string) => Parser<string>
	subString: (length: number) => Parser<string>
	string: typeof _string
	stringIn: (array: string[]) => any
	notString: (s: string) => Parser<string>
	charLiteral: Parser<string>
	stringLiteral: Parser<string>
	lowerCase: Parser<string>
	upperCase: Parser<string>
	UTF8_LETTER: typeof UTF8_LETTER
	OCCIDENTAL_LETTER: typeof OCCIDENTAL_LETTER
	ASCII_LETTER: typeof ASCII_LETTER
}

export declare const F: {
	parse: <T>(parse: IParser<T>['parse']) => Parser<T>
	nop: <T>() => Parser<T>
	try: <T>(p: Parser<T>) => Parser<T>
	any: Parser
	subStream: <T>(length: number) => Parser<T>
	not: <T>(p: Parser<T>) => Parser<T>
	lazy: <T>(p: Parser<T>, parameters?: any[], self?: object) => Parser<T>
	returns: <T>(v: T) => IParser<T>
	error: Parser
	eos: Parser
	satisfy: <T>(predicate: (value: T) => boolean) => Parser<T>
	sequence: <T>() => Parser<T>
	startsWith: <T>(value: T) => Parser<T>
	moveUntil: <T>(stop: string | string[] | Parser<T>) => Parser<T>
	dropTo: <T>(stop: string | string[] | Parser<T>) => Parser<T>
}

declare const N: {
	numberLiteral: Parser<number>
	digit: Parser<number>
	digits: Parser<number>
	integer: Parser<number>
}

declare const parsec: {
	parser: Parser
	response: {
		accept: <Value, X>(value: Value, stream: Stream<X>, offset: number, consumed: boolean) => Accept<Value>
		reject: (offset: number, consumed: boolean) => Reject
	}
}

export interface ExtractorBundleOptions {
	spacesCharacters: string
	wordSeparators?: Parser<string>
	letter: Parser<string>
	moreSeparators?: string
}

declare class ExtractorBundle {
	first: <T>(values: T[]) => T
	last: <T>(values: T[]) => T
	options: ExtractorBundleOptions
	constructor(options?: ExtractorBundleOptions)
	_handleOptions(options?: ExtractorBundleOptions): ExtractorBundleOptions
	spaces(): Parser<string>
	number(): Parser<number>
	digits(): Parser<string>
	word(): Parser<string>
	_wordSeparators(): Parser<string>
	words(keepSpaces?: boolean): Parser<string[]>
	wordsIn(array: string[], keepSpaces?: boolean): Parser<string[]>
}

declare const standard: {
	jsonParser: {
		parse: (source: any) => any
	}
	markdownDocument: {
		mdLine: () => Parser
		parseLine(line: any): any
		parse(stream: any, offset: any): any
	}
	markdownBundle: {
		blank: () => Parser
		rawTextUntilChar: (charList: any, allowVoid?: boolean) => Parser
		eol: () => Parser
		lineFeed: () => Parser
		fourSpacesBlock: () => Parser
		stop: () => Parser
		pureText: () => Parser
		italic: (pureTextParser: any) => Parser
		bold: (pureTextParser: any) => Parser
		code: (pureTextParser: any) => Parser
		text: (pureTextParser: any) => any
		formattedSequence: (pureTextParser: any, stopParser: any) => Parser
		formattedParagraph: () => Parser
		titleLine: () => Parser
		titleSharp: () => Parser
		title: () => Parser
		bulletLv1: () => Parser
		bulletLv2: () => Parser
		bullet: () => Parser
		codeLine: () => Parser
	}
	extractorBundle: typeof ExtractorBundle
	tokenBundle: {
		email: () => Parser
		date: () => Parser
		blank: (charsOrParser?: string) => any
		eol: Parser
	}
}

// eslint-disable-next-line @typescript-eslint/no-unused-vars
declare interface Stream<T> {
	endOfStream(index: number): boolean
	unsafeGet(index: number): T
}

declare class Stream<T> {
	constructor()
	location(index: number): number
	get(index: number): Try<T, Error>
	subStreamAt(s: string[], index: number): boolean
}

declare class StringStream extends Stream<string> {
	source: string
	constructor(source: string)
}

declare class ArrayStream<T> extends Stream<T> {
	source: T[]
	constructor(source: T[])
}

declare class ParserStream<T> extends Stream<T> {
	input: Stream<T>
	offsets: Record<number, number>
	source: Parser<T>
	constructor(parser: Parser<T>, source: Stream<T>)
	getOffset(index: number): number
}

declare class BufferedStream<T> extends Stream<T> {
	cache: Record<number, T>
	source: Stream<T>
	constructor(source: Stream<T>)
}

declare const Streams: {
	ofString: (source: string) => StringStream
	ofArray: <T>(source: T[]) => ArrayStream<T>
	ofParser: <T>(parser: Parser<T>, source: Stream<T>) => ParserStream<T>
	buffered: <T>(source: Stream<T>) => BufferedStream<T>
}

declare const JSON: {
	parse: (source: Stream<string>) => any
}

export declare const MD: {
	blank: () => Parser
	rawTextUntilChar: (charList: any, allowVoid?: boolean) => Parser
	eol: () => Parser
	lineFeed: () => Parser
	fourSpacesBlock: () => Parser
	stop: () => Parser
	pureText: () => Parser
	italic: (pureTextParser: any) => Parser
	bold: (pureTextParser: any) => Parser
	code: (pureTextParser: any) => Parser
	text: (pureTextParser: any) => any
	formattedSequence: (pureTextParser: any, stopParser: any) => Parser
	formattedParagraph: () => Parser
	titleLine: () => Parser
	titleSharp: () => Parser
	title: () => Parser
	bulletLv1: () => Parser
	bulletLv2: () => Parser
	bullet: () => Parser
	codeLine: () => Parser
}

export declare const X: typeof ExtractorBundle

export declare const T: {
	email: () => Parser
	date: () => Parser
	blank: (charsOrParser?: string) => any
	eol: Parser
}

export declare const bundle: {
	data: typeof data
	genlex: typeof genlex
	parsec: typeof parsec
	standard: typeof standard
	Streams: typeof Streams
	parser: Parser
	F: typeof F
	C: typeof C
	N: typeof N
	JSON: typeof JSON
	MD: typeof MD
	X: typeof X
	T: typeof T
}

export { JSON, Parser as parser, data, genlex, parsec, standard, Streams, bundle as default }

export {}
