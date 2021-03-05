/*
 * Copyright (c) 2010-2021 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2025-03-05
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

define(['./Parser'], function (Parser) {
	'use strict'

	const data = Symbol('data')

	/**
	 * Represents a chat command.
	 */
	class Command {
		constructor(_data) {
			this[data] = _data
		}

		getParameterParser() {
			return Parser.Rest
		}

		*autocomplete(parameterString) {}

		get id() {
			return this[data].commandID
		}

		get package() {
			return this[data].package
		}

		get identifier() {
			return this[data].identifier
		}

		get module() {
			return this[data].module
		}

		get isAvailable() {
			return this[data].isAvailable
		}
	}

	return Command
})
