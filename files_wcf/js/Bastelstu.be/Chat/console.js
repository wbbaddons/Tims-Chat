/*
 * Copyright (c) 2010-2018 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2022-08-16
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

define([ ], function () {
	"use strict";

	const start = Date.now()
	let last    = start

	const group = function () {
		if (window.console.group) window.console.group()
	}

	const groupCollapsed = function () {
		if (window.console.groupCollapsed) window.console.groupCollapsed()
	}

	const groupEnd = function () {
		if (window.console.groupEnd) window.console.groupEnd()
	}

	const println = function (type, ...args) {
		window.console[type](...args)
	}

	const log = function (...args) {
		println('log', ...args)
	}

	const warn = function (...args) {
		println('warn', ...args)
	}

	const error = function (...args) {
		println('error', ...args)
	}

	const debug = function (handler, ...args) {
		const now  = Date.now()
		const time = [ (now - start), `\t+${(now - last)}ms\t` ]

		if (args.length) {
			println('debug', ...time, `[${handler}]\t`, ...args)
		}
		else {
			println('debug', ...time, handler)
		}

		last = now
	}

	const debugException = function (error) {
		if (error instanceof Error) {
			let message  = `[${error.name}] „${error.message}“ in ${error.fileName} on line ${error.lineNumber}\n`

			if (error.stack) {
				message += 'Stacktrace:\n'
				message += error.stack
			}

			println('error', message)
		}
		else if (error.code && error.message) {
			debugAjaxException(error)
		}
	}

	const debugAjaxException = function (error) {
		groupCollapsed()
		let details = `[${error.code}] ${error.message}`

		const br2nl = (string) => string.split('\n')
		                                .map(line => line.replace(/<br\s*\/?>$/i, ''))
		                                .join('\n')

		if (error.stacktrace) {
			details += `\nStacktrace:\n${br2nl(error.stacktrace)}`
		}
		else if (error.exceptionID) {
			details += `\nException ID: ${error.exceptionID}`
		}

		println('debug', details)

		error.previous.forEach(previous => {
			let details = ''

			group()

			details += `${previous.message}\n`
			details += `Stacktrace:\n${br2nl(previous.stacktrace)}`

			println('debug', details)
		})

		error.previous.forEach(_ => groupEnd())
		groupEnd()
	}

	return { log
	       , warn
	       , error
	       , debug
	       , debugException
	       , group
	       , groupCollapsed
	       , groupEnd
	       }
});
