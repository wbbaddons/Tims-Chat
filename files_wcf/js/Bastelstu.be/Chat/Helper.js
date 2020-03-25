/*
 * Copyright (c) 2010-2018 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2024-03-25
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

define([ 'WoltLabSuite/Core/Date/Util'
       , 'WoltLabSuite/Core/Language'
       ], function (DateUtil, Language) {
	"use strict";

	class Helper {
		static deepFreeze(obj) {
			const propNames = Object.getOwnPropertyNames(obj)

			propNames.forEach(function (name) {
				let prop = obj[name]

				if (typeof prop === 'object' && prop !== null) Helper.deepFreeze(prop)
			})

			return Object.freeze(obj)
		}

		/**
		 * Returns true if the given element is an input[type=text],
		 * input[type=password] or textarea.
		 *
		 * @param  {Node} element
		 * @returns {boolean}
		 */
		static isInput(element) {
			if (element.tagName === 'INPUT') {
				if (element.getAttribute('type') !== 'text' && element.getAttribute('type') !== 'password') {
					return false
				}
			}
			else if (element.tagName !== 'TEXTAREA') {
				return false
			}

			return true
		}

		static throttle(fn, threshold = 250, scope) {
			let last = 0
			let deferTimer = null

			return function() {
				const now     = new Date().getTime()
				const args    = arguments
				const context = scope || this

				if (last && (now < (last + threshold))) {
					clearTimeout(deferTimer)

					return deferTimer = setTimeout(function() {
						last = now

						return fn.apply(context, args)
					}, threshold)
				}
				else {
					last = now

					return fn.apply(context, args)
				}
			}
		}

		/**
		 * Returns the caret position of the given element. If the element
		 * is not an input or textarea element -1 is returned.
		 *
		 * @param   {Node} element
		 * @returns {number}
		 */
		static getCaret(element) {
			if (!Helper.isInput(element)) throw new Error('Unsupported element')

			let position = 0

			if (element.selectionStart) {
				position = element.selectionStart
			}

			return position
		}

		static setCaret(element, position) {
			if (!Helper.isInput(element)) throw new Error('Unsupported element')

			if (element.selectionStart) {
				element.focus()
				element.setSelectionRange(position, position)
			}
		}

		static wrapElement(element, wrapper) {
			wrapper = wrapper || document.createElement('div')

			if (element.nextSibling) {
				element.parentNode.insertBefore(wrapper, element.nextSibling)
			}
			else {
				element.parentNode.appendChild(wrapper)
			}

			return wrapper.appendChild(element)
		}

		// Based on https://github.com/alexdunphy/flexText
		static makeFlexible(textarea) {
			if (textarea.tagName !== 'TEXTAREA') {
				throw new Error(`Unsupported element type: ${textarea.tagName}`)
			}

			const pre  = document.createElement('pre')
			const span = document.createElement('span')

			const mirror = function () {
				span.textContent = textarea.value
			}

			if (!textarea.parentNode.classList.contains('flexibleTextarea')) {
				Helper.wrapElement(textarea)
				textarea.parentNode.classList.add('flexibleTextarea')
			}

			textarea.classList.add('flexibleTextareaContent')
			pre.classList.add('flexibleTextareaMirror')

			pre.appendChild(span)
			pre.appendChild(document.createElement('br'))
			textarea.parentNode.insertBefore(pre, textarea)

			textarea.addEventListener('input',  mirror)
			mirror()
		}

		static getCircularArray(size) {
			class CircularArray extends Array {
				constructor(size) {
					super()

					Object.defineProperty(this, 'size', { enumerable: false
					                                    , value: size
					                                    , writable: false
					                                    , configurable: false
					                                    });
				}

				push() {
					super.push.apply(this, arguments)

					if (this.length > this.size) {
						super.shift()
					}

					return this.length;
				}

				unshift() {
					super.unshift.apply(this, arguments)

					if (this.length > this.size) {
						super.pop()
					}

					return this.length;
				}

				first() {
					return this[0]
				}

				last() {
					return this[this.length - 1]
				}
			}

			return new CircularArray(size)
		}

		static intToRGBHex(integer) {
			const r = ((integer >> 16) & 0xFF).toString(16)
			const g = ((integer >>  8) & 0xFF).toString(16)
			const b = ((integer >>  0) & 0xFF).toString(16)

			const rr = r.length == 1 ? `0${r}` : r
			const gg = g.length == 1 ? `0${g}` : g
			const bb = b.length == 1 ? `0${b}` : b

			return `#${rr}${gg}${bb}`
		}

		/**
		 * Returns the markup of a `time` element based on the given date just like a `time`
		 * element created by `wcf\system\template\plugin\TimeModifierTemplatePlugin`.
		 *
		 * @param	{Date}		date	displayed date
		 * @returns	{string}	`time` element
		 */
		static getTimeElementHTML(date) {
			const isFutureDate = date.getTime() > new Date().getTime()
			let dateTime = ''

			if (isFutureDate) {
				dateTime = DateUtil.formatDateTime(date)
			}

			// WSC 3.1
			if (typeof DateUtil.getTimeElement === 'function') {
				const elem = DateUtil.getTimeElement(date)

				// Work around a bug in DateUtil paired with Time/Relative
				if (isFutureDate) elem.innerText = dateTime

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
		 *
		 * @param  {Range} range Selection range
		 * @param  {Node}
		 * @return {Boolean}
		 */
		static rangeSpansTextContent(range, node) {
			const treeWalker = document.createTreeWalker(node, NodeFilter.SHOW_TEXT)

			let firstTextNode, lastTextNode
			while (treeWalker.nextNode()) {
				if (treeWalker.currentNode.nodeValue.trim() === '') continue

				if (!firstTextNode) {
					firstTextNode = treeWalker.currentNode
				}

				lastTextNode = treeWalker.currentNode
			}

			const nodeRange = range.cloneRange()
			if (firstTextNode) {
				nodeRange.setStart(firstTextNode, 0)
				nodeRange.setEnd(lastTextNode, lastTextNode.length)
			}
			else {
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
		 * @param  {Node} node
		 * @return {String}
		 */
		static getTextContent(node) {
			const acceptNode = node => {
				if (node instanceof Element) {
					if (node.tagName === 'SCRIPT' || node.tagName === 'STYLE') return NodeFilter.FILTER_REJECT
				}

				return NodeFilter.FILTER_ACCEPT
			}

			let out = ''

			const flags = NodeFilter.SHOW_TEXT | NodeFilter.SHOW_ELEMENT
			const treeWalker = document.createTreeWalker(node, flags, { acceptNode })

			const ignoredLinks = [ ]

			while (treeWalker.nextNode()) {
				const node = treeWalker.currentNode

				if (node instanceof Text) {
					if (node.parentNode.tagName === 'A' && ignoredLinks.indexOf(node.parentNode) >= 0) {
						continue
					}

					out += node.nodeValue.replace(/\n/g, '')
				}
				else {
					switch (node.tagName) {
						case 'IMG': {
							const alt = node.getAttribute('alt')

							if (node.classList.contains('smiley')) {
								out += ` ${alt} `
							}
							else if (alt && alt !== '') {
								out += ` ${alt} [Image ${node.src}] `
							}
							else {
								out += ` [Image ${node.src}] `
							}
						break }

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
							let link = node.href
							const text = node.textContent.trim()

							// handle named anchors
							if (text !== '' && text !== node.href) {
								ignoredLinks.push(node)

								let truncated = false

								if (text.indexOf('\u2026') >= 0) {
									const parts = text.split(/\u2026/)

									if (parts.length === 2) {
										truncated = node.href.startsWith(parts[0]) && node.href.endsWith(parts[1])
									}
								}

								if (!truncated) {
									link = `${node.textContent} [URL:${node.href}]`
								}
							}

							out += link
						break }
					}
				}
			}

			return out
		}
	}

	return Helper
});

