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
	'use strict';

	const DEPENDENCIES = [ 'UiSettingsButton' ]
	class Settings {
		constructor(modules) {
			this.modules = modules
			this.buttons = Array.from(elBySelAll('#chatQuickSettingsNavigation .button[data-module]'))
		}

		bootstrap() {
			this.buttons.forEach(element => {
				this.modules[element.dataset.module.replace(/\./g, '-')].instance(element).bootstrap()
			})
		}
	}
	Settings.DEPENDENCIES = DEPENDENCIES

	return Settings
});
