/*
 * Copyright (c) 2010-2026 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2030-10-20
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

import type { InstanceFactory } from 'Bastelstu.be/bottle'
import type Button from './Settings/Button'

const DEPENDENCIES = ['UiSettingsButton']
class Settings {
	public static readonly DEPENDENCIES = DEPENDENCIES

	modules: Record<string, InstanceFactory<Button, [HTMLElement]>>
	buttons: HTMLElement[]

	constructor(modules: Settings['modules']) {
		this.modules = modules
		this.buttons = Array.from(
			document.querySelectorAll(
				'#chatQuickSettingsNavigation .button[data-module]'
			)
		)
	}

	bootstrap(): void {
		this.buttons.forEach((element) => {
			this.modules[element.dataset.module!.replace(/\./g, '-')]
				.instance(element)
				.bootstrap()
		})
	}
}

export = Settings
