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

class Button {
	public static readonly DEPENDENCIES: string[] = []

	element: Element

	constructor(element: Element) {
		if (!(element as unknown) || !((element as unknown) instanceof Element)) {
			throw new Error('No DOM element provided')
		}

		this.element = element
	}

	bootstrap(): void {
		this.element.addEventListener('click', (event: MouseEvent) => {
			this.onClick(event)
		})
	}

	onClick(event: MouseEvent): void {
		event.preventDefault()
	}
}

export = Button
