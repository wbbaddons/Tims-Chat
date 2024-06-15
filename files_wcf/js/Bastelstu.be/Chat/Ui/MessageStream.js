/*
 * Copyright (c) 2010-2024 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2028-06-15
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

define([
	'../Helper',
	'WoltLabSuite/Core/Date/Util',
	'WoltLabSuite/Core/Dom/Change/Listener',
	'WoltLabSuite/Core/Language',
	'WoltLabSuite/Core/User',
	'WoltLabSuite/Core/Dom/Traverse',
	'../DataStructure/EventEmitter',
	'../DataStructure/RedBlackTree/Tree',
], function (
	Helper,
	DateUtil,
	DomChangeListener,
	Language,
	User,
	DOMTraverse,
	EventEmitter,
	Tree
) {
	'use strict'

	const enableAutoscroll = Symbol('enableAutoscroll')

	const DEPENDENCIES = []
	class MessageStream {
		constructor() {
			this.stream = elById('chatMessageStream')
			this.scrollContainer = elBySel('.scrollContainer', this.stream)

			this[enableAutoscroll] = true
			this.lastScrollPosition = undefined
			this.nodeMap = new WeakMap()
			this.positions = new Tree()
		}

		get enableAutoscroll() {
			return this[enableAutoscroll]
		}

		set enableAutoscroll(value) {
			this[enableAutoscroll] = value

			if (this[enableAutoscroll]) {
				this.scrollToBottom()
			}
		}

		bootstrap() {
			this.scrollContainer.addEventListener('copy', this.onCopy.bind(this))
			this.scrollContainer.addEventListener(
				'scroll',
				Helper.throttle(this.onScroll, 100, this),
				{ passive: true }
			)
		}

		getDateMarker(date) {
			const dateMarker = elCreate('li')
			dateMarker.classList.add('dateMarker')
			const time = elCreate('time')
			time.innerText = DateUtil.formatDate(date)
			time.setAttribute('datetime', DateUtil.format(date, 'Y-m-d'))
			dateMarker.appendChild(time)

			return dateMarker
		}

		onDifferentDays(a, b) {
			return DateUtil.format(a, 'Y-m-d') !== DateUtil.format(b, 'Y-m-d')
		}

		ingest(messages) {
			let scrollTopBefore = this.enableAutoscroll
				? 0
				: this.scrollContainer.scrollTop
			let prependedHeight = 0

			const ul = elBySel('ul', this.scrollContainer)
			const first = ul.firstElementChild

			const ingested = messages.map(
				function (item) {
					let currentScrollHeight = 0

					const li = elCreate('li')

					// Allow messages types to not render a messages
					// This can be used for status messages like ChatUpdate
					let fragment
					if ((fragment = item.getMessageType().render(item)) === false) return

					if (
						fragment.querySelector(
							`.userMention[data-user-id="${User.userId}"]`
						)
					)
						li.classList.add('mentioned')

					li.appendChild(fragment)

					li.classList.add('chatMessageBoundary')
					li.setAttribute('id', `message-${item.messageID}`)
					li.dataset.objectType = item.objectType
					li.dataset.userId = item.userID
					if (item.isOwnMessage()) li.classList.add('own')
					if (item.isDeleted) li.classList.add('tombstone')

					const position = this.positions.insert(item.messageID)
					if (position[1] !== undefined) {
						const sibling = elById(`message-${position[1]}`)
						if (!sibling) throw new Error('Unreachable')

						let nodeBefore, nodeAfter
						let dateMarkerBetween = false
						if (position[0] === 'LEFT') {
							nodeAfter = sibling
							nodeBefore = sibling.previousElementSibling

							if (nodeBefore && nodeBefore.classList.contains('dateMarker')) {
								elRemove(nodeBefore)
								nodeBefore = sibling.previousElementSibling
							}
						} else if (position[0] === 'RIGHT') {
							nodeBefore = sibling
							nodeAfter = sibling.nextElementSibling

							if (nodeAfter && nodeAfter.classList.contains('dateMarker')) {
								elRemove(nodeAfter)
								nodeAfter = sibling.nextElementSibling
							}
						} else {
							throw new Error('Unreachable')
						}

						const messageBefore = this.nodeMap.get(nodeBefore)
						if (nodeBefore && !messageBefore) throw new Error('Unreachable')
						const messageAfter = this.nodeMap.get(nodeAfter)
						if (nodeAfter && !messageAfter) throw new Error('Unreachable')

						if (!this.enableAutoscroll && nodeAfter)
							currentScrollHeight = this.scrollContainer.scrollHeight

						let context = nodeAfter
						if (nodeAfter) nodeAfter.classList.remove('first')
						if (messageBefore) {
							if (this.onDifferentDays(messageBefore.date, item.date)) {
								const dateMarker = this.getDateMarker(item.date)
								ul.insertBefore(dateMarker, nodeAfter)
								li.classList.add('first')
							} else {
								if (
									messageBefore.objectType !== item.objectType ||
									!item.getMessageType().joinable(messageBefore, item)
								) {
									li.classList.add('first')
								}
							}
						} else {
							li.classList.add('first')
						}
						if (messageAfter) {
							if (this.onDifferentDays(messageAfter.date, item.date)) {
								const dateMarker = this.getDateMarker(messageAfter.date)
								ul.insertBefore(dateMarker, nodeAfter)
								context = dateMarker
								nodeAfter.classList.add('first')
							} else {
								if (
									messageAfter.objectType !== item.objectType ||
									!item.getMessageType().joinable(item, messageAfter)
								) {
									nodeAfter.classList.add('first')
								}
							}
						}

						ul.insertBefore(li, context)

						if (!this.enableAutoscroll && nodeAfter) {
							prependedHeight +=
								this.scrollContainer.scrollHeight - currentScrollHeight
						}
					} else {
						li.classList.add('first')
						ul.insertBefore(li, null)
					}

					this.nodeMap.set(li, item)

					return { node: li, message: item }
				}.bind(this)
			)

			if (ingested.some((item) => item != null)) {
				if (this.enableAutoscroll) {
					this.scrollToBottom()
				} else {
					this.stream.classList.add('activity')
					this.scrollContainer.scrollTop = scrollTopBefore + prependedHeight
				}
			}

			DomChangeListener.trigger()

			this.emit('ingested', ingested)
		}

		scrollToBottom() {
			this.scrollContainer.scrollTop = this.scrollContainer.scrollHeight
			this.stream.classList.remove('activity')
		}

		onScroll() {
			const { scrollTop, scrollHeight, clientHeight } = this.scrollContainer
			const distanceFromTop = scrollTop
			const distanceFromBottom = scrollHeight - scrollTop - clientHeight

			let direction = 'down'

			if (
				this.lastScrollPosition != null &&
				scrollTop < this.lastScrollPosition
			) {
				direction = 'up'
			}

			if (direction === 'up') {
				if (distanceFromBottom > 7) {
					this.emit('scrollUp')
				}

				if (distanceFromTop <= 7) {
					this.emit('reachedTop')
				} else if (distanceFromTop <= 300) {
					this.emit('nearTop')
				}
			} else if (direction === 'down') {
				if (distanceFromTop > 7) {
					this.emit('scrollDown')
				}

				if (distanceFromBottom <= 7) {
					this.scrollToBottom()
					this.emit('reachedBottom')
				} else if (distanceFromBottom <= 300) {
					this.emit('nearBottom')
				}
			}

			this.lastScrollPosition = scrollTop
		}

		onCopy(event) {
			const selection = window.getSelection()

			// Similar to selecting nothing
			if (selection.isCollapsed) return

			// Get the first and last node in the selection
			let originalStart, start, end, originalEnd
			start = originalStart = selection.getRangeAt(0).startContainer
			end = originalEnd = selection.getRangeAt(
				selection.rangeCount - 1
			).endContainer

			const startOffset = selection.getRangeAt(0).startOffset
			const endOffset = selection.getRangeAt(selection.rangeCount - 1).endOffset

			// The Traverse module needs nodes of the Element type, the selected elements could be of type Text
			while (!(start instanceof Element) && start.parentNode)
				start = start.parentNode
			while (!(end instanceof Element) && end.parentNode) end = end.parentNode

			if (!start || !end)
				throw new Error('Unexpected error, no element nodes in selection')

			// Try to find the starting li element in the selection
			if (!start.id || start.id.indexOf('message-') !== 0) {
				start = DOMTraverse.parentBySel(start, "li[id^='message']", this.stream)
			}

			// Try to find the ending li element in the selection
			if (!end.id || end.id.indexOf('message-') !== 0) {
				end = DOMTraverse.parentBySel(end, "li[id^='message']", this.stream)
			}

			// Do not select a message if we selected only a new line
			if (
				originalStart instanceof Text &&
				originalStart.textContent.substring(startOffset) === ''
			) {
				start = DOMTraverse.next(start)
			}

			// The selection went outside of the stream container, end at the last li element
			if (end === null) {
				end = elBySel('ul > li:last-child', this.stream)
			}

			// Discard the selection, we selected only whitespace between two messages
			if (start === end && endOffset === 0) return

			// Do not include the ending message if there is no visible selection
			if (start !== end && endOffset === 0) {
				end = DOMTraverse.prev(end)
			}

			const elements = []
			let next = start

			do {
				elements.push(next)

				if (next === end) break
			} while ((next = DOMTraverse.next(next)))

			// Only apply our custom formatting when selecting multiple or whole messages
			if (elements.length === 1) {
				const range = document.createRange()
				range.setStart(originalStart, startOffset)
				range.setEnd(originalEnd, endOffset)

				if (
					!Helper.rangeSpansTextContent(
						range,
						start.querySelector('.chatMessage')
					)
				)
					return
			}

			try {
				event.clipboardData.setData(
					'text/plain',
					elements
						.map((el, index, arr) => {
							const message = this.nodeMap.get(el)

							if (el.classList.contains('dateMarker'))
								return `== ${el.textContent.trim()} ==`

							if (!message) return

							if (el.classList.contains('tombstone')) {
								return `[${message.formattedTime}] ${Language.get(
									'chat.messageType.be.bastelstu.chat.messageType.tombstone.message'
								)}`
							}

							const elem = elBySel('.chatMessage', el)

							let body
							if (
								typeof (body = message
									.getMessageType()
									.renderPlainText(message)) === 'undefined' ||
								body === false
							) {
								body = Helper.getTextContent(elem)
									.replace(/\t+/g, '\t') // collapse multiple tabs
									.replace(/ +/g, ' ') // collapse multiple spaces
									.replace(/([\t ]*\n){2,}/g, '\n') // collapse line consisting of tabs, spaces and newlines
									.replace(/^[\t ]+|[\t ]+$/gm, '') // remove leading and trailing whitespace per line
							}

							return `[${message.formattedTime}] <${
								message.username
							}> ${body.trim()}`
						})
						.filter((x) => x)
						.join('\n')
				)

				event.preventDefault()
			} catch (e) {
				console.error('Unable to use the clipboard API')
				console.error(e)
			}
		}
	}
	EventEmitter(MessageStream.prototype)
	MessageStream.DEPENDENCIES = DEPENDENCIES

	return MessageStream
})
