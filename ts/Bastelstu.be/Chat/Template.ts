/*
 * Copyright (c) 2010-2025 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2029-04-23
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

import CoreTemplate from 'WoltLabSuite/Core/Template'

type Templates = Record<string, Template>

/**
 * Template extends WoltLab Suite's Templates by passing in a list of
 * re-usable sub-templates.
 */
class Template extends CoreTemplate {
	constructor(string: string, private templates: Templates = {}) {
		super(string)

		// Called with the correct context below
		// eslint-disable-next-line @typescript-eslint/unbound-method
		const oldFetch = this.fetch

		this.fetch = (variables: { t?: Templates }) => {
			variables = Object.assign({}, variables)

			const templates = Object.assign({}, this.templates, variables.t || {})
			variables.t = templates

			return oldFetch.call(this, variables)
		}
	}
}

export = Template
