/*
 * Copyright (c) 2010-2018 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2024-10-20
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

define([ '../Command'
       , '../Parser'
       ], function (Command, Parser) {
	"use strict";

	class Away extends Command {
		getParameterParser() {
			return Parser.Rest.map(reason => ({ reason }))
		}
	}

	return Away
});
