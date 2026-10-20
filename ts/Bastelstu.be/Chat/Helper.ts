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

import * as DateUtil from 'WoltLabSuite/Core/Date/Util'

function deepFreeze<T>(obj: T): T {
	const propNames = Object.getOwnPropertyNames(obj) as [keyof T]

	propNames.forEach(function (name) {
		const prop = obj[name]

		if (typeof prop === 'object' && prop !== null) {
			Helper.deepFreeze(prop)
		}
	})

	return Object.freeze(obj)
}

/**
 * Returns true if the given element is an input[type=text],
 * input[type=password] or textarea.
 */
function isInput(element: HTMLElement): boolean {
	if (element.tagName === 'INPUT') {
		if (
			element.getAttribute('type') !== 'text' &&
			element.getAttribute('type') !== 'password'
		) {
			return false
		}
	} else if (element.tagName !== 'TEXTAREA') {
		return false
	}

	return true
}

function throttle(
	fn: (...args: unknown[]) => unknown,
	threshold = 250,
	scope: unknown
) {
	let last = 0
	let deferTimer: number | null = null

	return function (this: unknown, ...args: unknown[]): void {
		const now = new Date().getTime()

		const context = scope || this

		if (last && now < last + threshold) {
			if (deferTimer !== null) {
				clearTimeout(deferTimer)
			}

			deferTimer = setTimeout(function (): void {
				last = now

				fn.apply(context, args)
			}, threshold)
		} else {
			last = now

			fn.apply(context, args)
		}
	}
}

/**
 * Returns the caret position of the given element. If the element
 * is not an input or textarea element -1 is returned.
 */
function getCaret(element: HTMLInputElement | HTMLTextAreaElement): number {
	if (!isInput(element)) {
		throw new Error('Unsupported element')
	}

	let position = 0

	if (element.selectionStart != null) {
		position = element.selectionStart
	}

	return position
}

function setCaret(
	element: HTMLInputElement | HTMLTextAreaElement,
	position: number
): void {
	if (!isInput(element)) {
		throw new Error('Unsupported element')
	}

	if (element.selectionStart != null) {
		element.focus()
		element.setSelectionRange(position, position)
	}
}

function wrapElement(element: HTMLElement, wrapper?: HTMLElement): HTMLElement {
	wrapper = wrapper || document.createElement('div')

	if (element.parentNode) {
		element.parentNode.insertBefore(wrapper, element)
	}

	return wrapper.appendChild(element)
}

// Based on https://github.com/alexdunphy/flexText
function makeFlexible(textarea: HTMLTextAreaElement): void {
	if (textarea.tagName !== 'TEXTAREA') {
		throw new Error(`Unsupported element type: ${textarea.tagName}`)
	}

	const pre = document.createElement('pre')
	const span = document.createElement('span')

	const mirror = function () {
		span.textContent = textarea.value
	}

	if (
		!textarea.parentElement ||
		!textarea.parentElement.classList.contains('flexibleTextarea')
	) {
		Helper.wrapElement(textarea)

		textarea.parentElement!.classList.add('flexibleTextarea')
	}

	textarea.removeAttribute('rows')
	textarea.classList.add('flexibleTextareaContent')
	pre.classList.add('flexibleTextareaMirror')

	pre.appendChild(span)
	pre.appendChild(document.createElement('br'))
	textarea.parentElement!.insertBefore(pre, textarea)

	textarea.addEventListener('input', mirror)
	mirror()
}

class CircularArray<T> extends Array<T> {
	readonly size: number

	constructor(size: number) {
		super()

		Object.defineProperty(this, 'size', {
			enumerable: false,
			value: size,
			writable: false,
			configurable: false,
		})
	}

	push(...items: T[]): number {
		super.push.apply(this, items)

		if (this.length > this.size) {
			super.shift()
		}

		return this.length
	}

	unshift(...items: T[]): number {
		super.unshift.apply(this, items)

		if (this.length > this.size) {
			super.pop()
		}

		return this.length
	}

	first(): T {
		return this[0]
	}

	last(): T {
		return this[this.length - 1]
	}
}

function getCircularArray<T>(size: number): CircularArray<T> {
	return new CircularArray(size)
}

function intToRGBHex(integer: number): string {
	const r = ((integer >> 16) & 0xff).toString(16)
	const g = ((integer >> 8) & 0xff).toString(16)
	const b = ((integer >> 0) & 0xff).toString(16)

	const rr = r.length == 1 ? `0${r}` : r
	const gg = g.length == 1 ? `0${g}` : g
	const bb = b.length == 1 ? `0${b}` : b

	return `#${rr}${gg}${bb}`
}

/**
 * Returns the markup of a `time` element based on the given date just like a `time`
 * element created by `wcf\system\template\plugin\TimeModifierTemplatePlugin`.
 */
function getTimeElementHTML(date: Date): string {
	const isFutureDate = date.getTime() > new Date().getTime()
	let dateTime = ''

	if (isFutureDate) {
		dateTime = DateUtil.formatDateTime(date)
	}

	// WSC 3.1
	if (typeof DateUtil.getTimeElement === 'function') {
		const elem = DateUtil.getTimeElement(date)

		// Work around a bug in DateUtil paired with Time/Relative
		if (isFutureDate) {
			elem.innerText = dateTime
		}

		return elem.outerHTML
	}

	return `<time class="datetime"
		      datetime="${DateUtil.format(date, 'c')}"
		      data-date="${DateUtil.formatDate(date)}"
		      data-time="${DateUtil.formatTime(date)}"
		      data-offset="${date.getTimezoneOffset() * 60}"
		      data-timestamp="${(date.getTime() - date.getMilliseconds()) / 1000}"
		      ${isFutureDate ? 'data-is-future-date="true"' : ''}
		>${dateTime}</time>`
}

/**
 * Returns whether the supplied selection range covers the whole text inside the given node
 *
 * Source: https://stackoverflow.com/a/27686686/1112384
 */
function rangeSpansTextContent(range: Range, node: Node): boolean {
	// Create an iterator over Text nodes
	const treeWalker = document.createTreeWalker(node, NodeFilter.SHOW_TEXT)

	let firstTextNode: Text | null = null
	let lastTextNode: Text | null = null

	while (treeWalker.nextNode()) {
		if (treeWalker.currentNode.nodeValue!.trim() === '') {
			continue
		}

		if (!firstTextNode) {
			firstTextNode = treeWalker.currentNode as Text
		}

		lastTextNode = treeWalker.currentNode as Text
	}

	const nodeRange = range.cloneRange()
	if (firstTextNode && lastTextNode) {
		nodeRange.setStart(firstTextNode, 0)
		nodeRange.setEnd(lastTextNode, lastTextNode.length)
	} else {
		nodeRange.selectNodeContents(node)
	}

	const bp1 = range.compareBoundaryPoints(Range.START_TO_START, nodeRange)
	const bp2 = range.compareBoundaryPoints(Range.END_TO_END, nodeRange)

	return bp1 < 1 && bp2 > -1
}

/**
 * Returns the text of a node and its children.
 *
 * @see {@link https://github.com/WoltLab/WCF/blob/a20be4267fc711299d6bde7c34a8b36199ae393f/wcfsetup/install/files/js/WCF.Message.js#L1180-L1264}
 */
function getTextContent(node: Node): string {
	const acceptNode = (node: Node) => {
		if (node instanceof Element) {
			if (node.tagName === 'SCRIPT' || node.tagName === 'STYLE') {
				return NodeFilter.FILTER_REJECT
			}
		}

		return NodeFilter.FILTER_ACCEPT
	}

	let out = ''

	const flags = NodeFilter.SHOW_TEXT | NodeFilter.SHOW_ELEMENT
	const treeWalker = document.createTreeWalker(node, flags, { acceptNode })

	const ignoredLinks: HTMLElement[] = []

	while (treeWalker.nextNode()) {
		const node = treeWalker.currentNode as HTMLElement | Text

		if (node instanceof Text) {
			const parent = node.parentElement

			if (parent?.tagName === 'A' && ignoredLinks.indexOf(parent) >= 0) {
				continue
			}

			out += node.nodeValue!.replace(/\n/g, ' ')
		} else {
			switch (node.tagName) {
				case 'IMG': {
					const alt = node.getAttribute('alt')

					if (node.classList.contains('smiley')) {
						out += ` ${alt} `
					} else if (alt != null && alt !== '') {
						out += ` ${alt} [Image ${(node as HTMLImageElement).src}] `
					} else {
						out += ` [Image ${(node as HTMLImageElement).src}] `
					}
					break
				}

				case 'BR':
				case 'LI':
				case 'UL':
				case 'DIV':
				case 'TR':
					out += '\n'
					break

				case 'TH':
				case 'TD':
					out += '\t'
					break

				case 'P':
					out += '\n\n'
					break

				case 'A': {
					const anchor = node as HTMLAnchorElement
					let link = anchor.href
					const text = anchor.textContent!.trim()

					// handle named anchors
					if (text !== '' && text !== anchor.href) {
						ignoredLinks.push(anchor)

						let truncated = false

						if (text.indexOf('\u2026') >= 0) {
							const parts = text.split(/\u2026/)

							if (parts.length === 2) {
								truncated =
									anchor.href.startsWith(parts[0]) &&
									anchor.href.endsWith(parts[1])
							}
						}

						if (!truncated) {
							link = `${anchor.textContent} [URL:${anchor.href}]`
						}
					}

					out += link
					break
				}
			}
		}
	}

	return out
}

function getElementSiblings(element: HTMLElement): Element[] {
	if (!element.parentNode) {
		return []
	}

	const children = [...element.parentNode.children]
	const index = children.indexOf(element)

	return [...children.slice(0, index), ...children.slice(index + 1)]
}

const Helper = {
	deepFreeze,
	isInput,
	throttle,
	getCaret,
	setCaret,
	wrapElement,
	makeFlexible,
	getCircularArray,
	intToRGBHex,
	getTimeElementHTML,
	rangeSpansTextContent,
	getTextContent,
	getElementSiblings,
}

export = Helper
