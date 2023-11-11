/*
 * Copyright (c) 2010-2024 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2028-01-13
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

define([
	'WoltLabSuite/Core/Dom/Traverse',
	'WoltLabSuite/Core/Dom/Util',
	'WoltLabSuite/Core/Ui/Dropdown/Simple',
], function (DomTraverse, DomUtil, SimpleDropdown) {
	'use strict'

	const DEPENDENCIES = [
		'ProfileStore',
		'Template.UserListDropdownMenuItems',
		'bottle',
	]
	class UserActionDropdownHandler {
		constructor(profiles, dropdownTemplate, bottle) {
			this.profiles = profiles
			this.dropdownTemplate = dropdownTemplate
			this.bottle = bottle

			this.container = elById('main')
		}

		bootstrap() {
			this.container.addEventListener('click', this.onClick.bind(this))
		}

		onClick(event) {
			const userElement = event.target.classList.contains(
				'jsUserActionDropdown'
			)
				? event.target
				: DomTraverse.parentByClass(
						event.target,
						'jsUserActionDropdown',
						this.container
				  )

			if (!userElement) return

			event.preventDefault()
			event.stopPropagation()

			const user = this.profiles.get(parseInt(userElement.dataset.userId, 10))
			if (user == null) {
				throw new Error('Unreachable')
			}

			// Note: We would usually use firstElementChild here, but this
			//       is not supported in Safari and Edge
			const dropdown = DomUtil.createFragmentFromHtml(
				this.dropdownTemplate.fetch({ user })
			).querySelector('*')

			Array.from(elBySelAll('[data-module]', dropdown)).forEach((element) => {
				const moduleName = element.dataset.module
				let userAction
				if (
					!this.bottle.container.UserAction ||
					(userAction =
						this.bottle.container.UserAction[
							`${moduleName.replace(/\./g, '-')}`
						]) == null
				) {
					this.bottle.factory(
						`UserAction.${moduleName.replace(/\./g, '-')}`,
						(_) => {
							const UserAction = require(moduleName)
							const deps = this.bottle.digest(UserAction.DEPENDENCIES || [])

							return new UserAction(...deps)
						}
					)

					userAction =
						this.bottle.container.UserAction[
							`${moduleName.replace(/\./g, '-')}`
						]
				}

				element.addEventListener(WCF_CLICK_EVENT, (event) =>
					userAction.onClick(user, event)
				)
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
	UserActionDropdownHandler.DEPENDENCIES = DEPENDENCIES

	return UserActionDropdownHandler
})
