/*
 * Copyright (c) 2010-2021 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2025-02-04
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

define(['WoltLabSuite/Core/Template'], function (_Template) {
	'use strict'

	/**
	 * Template extends WoltLab Suite's Templates by passing in a list of
	 * re-usable sub-templates.
	 */
	class Template extends _Template {
		constructor(string, templates = {}) {
			super(string)

			this.templates = templates

			const oldFetch = this.fetch
			this.fetch = function (variables) {
				variables = Object.assign({}, variables)

				const templates = Object.assign({}, this.templates, variables.t || {})
				variables.t = templates

				return oldFetch(variables)
			}.bind(this)
		}
	}

	return Template
})
