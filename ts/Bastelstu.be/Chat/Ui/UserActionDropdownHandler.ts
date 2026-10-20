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
import * as DomTraverse from 'WoltLabSuite/Core/Dom/Traverse'
import DomUtil from 'WoltLabSuite/Core/Dom/Util'
import * as SimpleDropdown from 'WoltLabSuite/Core/Ui/Dropdown/Simple'

import type ProfileStore from '../ProfileStore'
import type Template from '../Template'
import type Bottle from 'Bastelstu.be/bottle'
import type UserAction from './UserActions/Action'

const DEPENDENCIES = [
	'ProfileStore',
	'Template.UserListDropdownMenuItems',
	'bottle',
]
class UserActionDropdownHandler {
	public static readonly DEPENDENCIES = DEPENDENCIES

	profiles: ProfileStore
	dropdownTemplate: Template
	bottle: Bottle & {
		container: {
			UserAction?: Record<string, Promise<UserAction> | undefined>
		}
	}
	container: HTMLElement

	constructor(
		profiles: ProfileStore,
		dropdownTemplate: Template,
		bottle: Bottle
	) {
		this.profiles = profiles
		this.dropdownTemplate = dropdownTemplate
		this.bottle = bottle

		this.container = document.getElementById('main')!
	}

	bootstrap(): void {
		this.container.addEventListener('click', (ev) => {
			this.onClick(ev)
		})
	}

	onClick(event: MouseEvent): void {
		const userElement = (event.target as HTMLElement).classList.contains(
			'jsUserActionDropdown'
		)
			? (event.target as HTMLElement)
			: (DomTraverse.parentByClass(
					event.target as HTMLElement,
					'jsUserActionDropdown',
					this.container
			  ) as HTMLElement | null)

		if (!userElement) {
			return
		}

		event.preventDefault()
		event.stopPropagation()

		const user = this.profiles.get(parseInt(userElement.dataset.userId!, 10))
		if (user == null) {
			throw new Error('Unreachable')
		}

		// Note: We would usually use firstElementChild here, but this
		//       is not supported in Safari and Edge
		const dropdown = DomUtil.createFragmentFromHtml(
			this.dropdownTemplate.fetch({ user })
		).querySelector<HTMLElement>('*')!

		dropdown
			.querySelectorAll('[data-module]')
			.forEach((element: HTMLElement) => {
				const moduleName = element.dataset.module!

				let userAction: Promise<UserAction> | undefined

				if (
					(userAction =
						this.bottle.container.UserAction?.[
							`${moduleName.replace(/\./g, '-')}`
						]) == null
				) {
					this.bottle.factory(
						`UserAction.${moduleName.replace(/\./g, '-')}`,
						async (_): Promise<UserAction> => {
							const mod: typeof UserAction = (await import(moduleName)).default

							const deps = this.bottle.digest(mod.DEPENDENCIES) as unknown[]

							return new (mod as any)(...deps)
						}
					)

					userAction =
						this.bottle.container.UserAction![
							`${moduleName.replace(/\./g, '-')}`
						]!
				}

				void userAction.then((userAction) => {
					element.addEventListener('click', (event) => {
						userAction.onClick(user, event)
					})
				})
			})

		SimpleDropdown.initFragment(userElement, dropdown)
		SimpleDropdown.registerCallback(userElement.id, (container, action) => {
			if (action === 'close') {
				SimpleDropdown.destroy(container)
			}
		})
		SimpleDropdown.toggleDropdown(userElement.id)
	}
}

export = UserActionDropdownHandler
