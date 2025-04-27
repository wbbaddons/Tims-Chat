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

import { type AjaxResponseException } from '@woltlab/d.ts/WoltLabSuite/Core/Ajax/Data'

const start = Date.now()
let last = start

const group = function (...data: unknown[]): void {
	window.console.group(data)
}

const groupCollapsed = function (...data: unknown[]): void {
	window.console.groupCollapsed(data)
}

const groupEnd = function (): void {
	window.console.groupEnd()
}

const println = function (
	type: 'debug' | 'error' | 'info' | 'log' | 'trace' | 'warn',
	...args: unknown[]
): void {
	window.console[type](...args)
}

const log = function (...args: unknown[]): void {
	println('log', ...args)
}

const warn = function (...args: unknown[]): void {
	println('warn', ...args)
}

const error = function (...args: unknown[]): void {
	println('error', ...args)
}

const debug = function (handler: string, ...args: unknown[]) {
	const now = Date.now()
	const time = [now - start, `\t+${now - last}ms\t`]

	if (args.length > 0) {
		println('debug', ...time, `[${handler}]\t`, ...args)
	} else {
		println('debug', ...time, handler)
	}

	last = now
}

const debugException = function (
	error:
		| (Error & { fileName?: string; lineNumber?: string })
		| (AjaxResponseException & { code: number })
): void {
	if (error instanceof Error) {
		let message = `[${error.name}] „${error.message}“`

		// Firefox supports additional information
		const fileName = error.fileName
		const lineNumber = error.lineNumber
		if (fileName != null && lineNumber != null) {
			message += ` in ${fileName} on line ${lineNumber}`
		}

		message += '\n'

		if (error.stack != null) {
			message += 'Stacktrace:\n'
			message += error.stack
		}

		println('error', message)
	} else if (error.code && error.message) {
		debugAjaxException(error)
	}
}

const debugAjaxException = function (error: AjaxResponseException): void {
	groupCollapsed()
	let details = `[${error.code}] ${error.message}`

	const br2nl = (string: string) =>
		string
			.split('\n')
			.map((line) => line.replace(/<br\s*\/?>$/i, ''))
			.join('\n')

	if (error.stacktrace != null) {
		details += `\nStacktrace:\n${br2nl(error.stacktrace)}`
	} else if (error.exceptionID != null) {
		details += `\nException ID: ${error.exceptionID}`
	}

	println('debug', details)

	if (error.previous) {
		error.previous.forEach((previous) => {
			let details = ''

			group()

			details += `${previous.message}\n`
			details += `Stacktrace:\n${br2nl(previous.stacktrace)}`

			println('debug', details)
		})

		error.previous.forEach((_) => {
			groupEnd()
		})
	}

	groupEnd()
}

export = {
	log,
	warn,
	error,
	debug,
	debugException,
	group,
	groupCollapsed,
	groupEnd,
}
