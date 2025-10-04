/*
 * Copyright (c) 2010-2025 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2029-10-04
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

import Helper from '../Helper'
import * as DateUtil from 'WoltLabSuite/Core/Date/Util'
import DomChangeListener from 'WoltLabSuite/Core/Dom/Change/Listener'
import * as Language from 'WoltLabSuite/Core/Language'
import User from 'WoltLabSuite/Core/User'
import * as DOMTraverse from 'WoltLabSuite/Core/Dom/Traverse'
import EventEmitter from '../DataStructure/EventEmitter'
import Tree from '../DataStructure/RedBlackTree/Tree'
import { Side } from '../DataStructure/RedBlackTree/Helper'
import console from '../console'

import type Message from '../Message'

// Convince TypeScript that this class is an EventEmitter,
// this is done by calling EventEmitter(Input.prototype) after the class definition
// eslint-disable-next-line @typescript-eslint/no-unsafe-declaration-merging, @typescript-eslint/no-empty-object-type
interface MessageStream extends EventEmitter<MessageStream> {
	on(
		type: 'ingested',
		listener: ({
			target,
			detail,
		}: {
			target: MessageStream
			detail: { message: Message; node: HTMLElement }[]
		}) => void
	)
	on(
		type: 'scrollUp',
		listener: ({
			target,
			detail,
		}: {
			target: MessageStream
			detail: unknown
		}) => void
	)
	on(
		type: 'reachedBottom',
		listener: ({
			target,
			detail,
		}: {
			target: MessageStream
			detail: unknown
		}) => void
	)
}

// eslint-disable-next-line @typescript-eslint/no-unsafe-declaration-merging
class MessageStream {
	stream: HTMLElement
	scrollContainer: HTMLElement
	#enableAutoscroll: boolean
	lastScrollPosition: number | undefined
	nodeMap: WeakMap<Element, Message>
	positions: Tree

	constructor() {
		this.stream = document.getElementById('chatMessageStream')!
		this.scrollContainer = this.stream.querySelector('.scrollContainer')!

		this.#enableAutoscroll = true
		this.lastScrollPosition = undefined
		this.nodeMap = new WeakMap()
		this.positions = new Tree()
	}

	get enableAutoscroll(): boolean {
		return this.#enableAutoscroll
	}

	set enableAutoscroll(value: boolean) {
		this.#enableAutoscroll = value

		if (this.#enableAutoscroll) {
			this.scrollToBottom()
		}
	}

	bootstrap(): void {
		this.scrollContainer.addEventListener('copy', (event) => {
			this.onCopy(event)
		})

		this.scrollContainer.addEventListener(
			'scroll',
			Helper.throttle(
				() => {
					this.onScroll()
				},
				100,
				this
			),
			{ passive: true }
		)
	}

	getDateMarker(date: Date): HTMLElement {
		const dateMarker = document.createElement('li')
		dateMarker.classList.add('dateMarker')
		const time = document.createElement('time')
		time.innerText = DateUtil.formatDate(date)
		time.setAttribute('datetime', DateUtil.format(date, 'Y-m-d'))
		dateMarker.appendChild(time)

		return dateMarker
	}

	onDifferentDays(a: Date, b: Date): boolean {
		return DateUtil.format(a, 'Y-m-d') !== DateUtil.format(b, 'Y-m-d')
	}

	ingest(messages: Message[]): void {
		const scrollTopBefore = this.enableAutoscroll
			? 0
			: this.scrollContainer.scrollTop
		let prependedHeight = 0

		const ul = this.scrollContainer.querySelector('ul')!

		const ingested = messages.map((item: Message) => {
			let currentScrollHeight = 0

			const li = document.createElement('li')

			// Allow messages types to not render a messages
			// This can be used for status messages like ChatUpdate
			let fragment: DocumentFragment | false
			if ((fragment = item.getMessageType().render(item)) === false) {
				return
			}

			if (
				fragment.querySelector(`.userMention[data-object-id="${User.userId}"]`)
			) {
				li.classList.add('mentioned')
			}

			li.appendChild(fragment)

			li.classList.add('chatMessageBoundary')
			li.setAttribute('id', `message-${item.messageID}`)
			li.dataset.objectType = item.objectType
			li.dataset.userId = item.userID as unknown as string
			if (item.isOwnMessage()) li.classList.add('own')
			if (item.isDeleted) li.classList.add('tombstone')

			const position = this.positions.insert(item.messageID)
			if (position[1] !== undefined) {
				const sibling = document.getElementById(`message-${position[1]}`)
				if (!sibling) {
					throw new Error('Unreachable')
				}

				let nodeBefore: Element | null
				let nodeAfter: Element | null

				if (position[0] === Side.Left) {
					nodeAfter = sibling
					nodeBefore = sibling.previousElementSibling

					if (nodeBefore && nodeBefore.classList.contains('dateMarker')) {
						nodeBefore.remove()
						nodeBefore = sibling.previousElementSibling
					}
				} else if (position[0] === Side.Right) {
					nodeBefore = sibling
					nodeAfter = sibling.nextElementSibling

					if (nodeAfter && nodeAfter.classList.contains('dateMarker')) {
						nodeAfter.remove()
						nodeAfter = sibling.nextElementSibling
					}
				} else {
					throw new Error('Unreachable')
				}

				const messageBefore = this.nodeMap.get(nodeBefore!)
				if (nodeBefore && !messageBefore) {
					throw new Error('Unreachable')
				}
				const messageAfter = this.nodeMap.get(nodeAfter!)
				if (nodeAfter && !messageAfter) {
					throw new Error('Unreachable')
				}

				if (!this.enableAutoscroll && nodeAfter) {
					currentScrollHeight = this.scrollContainer.scrollHeight
				}

				let context = nodeAfter
				if (nodeAfter) {
					nodeAfter.classList.remove('first')
				}
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
					// nodeAfter must exist because we found `messageAfter` in our WeakMap for this node
					if (this.onDifferentDays(messageAfter.date, item.date)) {
						const dateMarker = this.getDateMarker(messageAfter.date)
						ul.insertBefore(dateMarker, nodeAfter)
						context = dateMarker
						nodeAfter!.classList.add('first')
					} else {
						if (
							messageAfter.objectType !== item.objectType ||
							!item.getMessageType().joinable(item, messageAfter)
						) {
							nodeAfter!.classList.add('first')
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
		})

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

	scrollToBottom(): void {
		this.scrollContainer.scrollTop = this.scrollContainer.scrollHeight
		this.stream.classList.remove('activity')
	}

	onScroll(): void {
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

	findParentElement(node: Node): Element | null {
		while (!(node instanceof Element) && node.parentNode) {
			node = node.parentNode
		}

		if (!(node instanceof Element)) {
			return null
		}

		return node
	}

	onCopy(event: ClipboardEvent): void {
		if (!event.clipboardData) {
			return
		}

		const selection = window.getSelection()

		// Similar to selecting nothing
		if (!selection || selection.isCollapsed) {
			return
		}

		// Get the first and last node in the selection
		const originalStart = selection.getRangeAt(0).startContainer
		const originalEnd = selection.getRangeAt(
			selection.rangeCount - 1
		).endContainer

		const startOffset = selection.getRangeAt(0).startOffset
		const endOffset = selection.getRangeAt(selection.rangeCount - 1).endOffset

		// The Traverse module needs nodes of the Element type, the selected elements could be of type Text
		let start = this.findParentElement(originalStart)
		let end = this.findParentElement(originalEnd)

		if (!start || !end) {
			throw new Error('Unexpected error, no element nodes in selection')
		}

		// Try to find the starting li element in the selection
		if (!start.id || !start.id.startsWith('message-')) {
			start = DOMTraverse.parentBySel(start, "li[id^='message']", this.stream)
		}

		// Try to find the ending li element in the selection
		if (!end.id || !end.id.startsWith('message-')) {
			end = DOMTraverse.parentBySel(end, "li[id^='message']", this.stream)
		}

		// Do not select a message if we selected only a new line
		if (
			originalStart instanceof Text &&
			originalStart.textContent!.substring(startOffset) === ''
		) {
			if (start) {
				// start must be an Element:
				// Either it got rejected by the instanceof checks above or
				// it is the return value of DOMTraverse.parentBySel
				start = DOMTraverse.next(start)
			}
		}

		// The selection went outside of the stream container, end at the last li element
		if (end === null) {
			end = this.stream.querySelector('ul > li:last-child')
		}

		// Discard the selection, we selected only whitespace between two messages
		if (start === end && endOffset === 0) {
			return
		}

		// Do not include the ending message if there is no visible selection
		if (start !== end && endOffset === 0) {
			if (end) {
				// end must be an Element:
				// Either it got rejected by the instanceof checks above or
				// it is the return value of DOMTraverse.parentBySel or window.elBySel
				end = DOMTraverse.prev(end)
			} else {
				console.debug('MessageStream.onCopy', '`end` is null')
			}
		}

		const elements: Element[] = []
		let next = start

		// TODO: assert(next != null)
		do {
			elements.push(next!)

			if (next === end) {
				break
			}
		} while ((next = DOMTraverse.next(next!)))

		// Only apply our custom formatting when selecting multiple or whole messages
		if (elements.length === 1) {
			const range = document.createRange()
			range.setStart(originalStart, startOffset)
			range.setEnd(originalEnd, endOffset)

			const message = start!.querySelector('.chatMessage')!
			if (!Helper.rangeSpansTextContent(range, message)) {
				return
			}
		}

		try {
			event.clipboardData.setData(
				'text/plain',
				elements
					.map((el, _index, _arr) => {
						if (el.classList.contains('dateMarker')) {
							return `== ${el.textContent!.trim()} ==`
						}

						const message = this.nodeMap.get(el)

						if (!message) {
							return
						}

						if (el.classList.contains('tombstone')) {
							return `[${message.formattedTime}] ${Language.get(
								'chat.messageType.be.bastelstu.chat.messageType.tombstone.message'
							)}`
						}

						const elem = el.querySelector('.chatMessage')!

						let body = message.getMessageType().renderPlainText(message)
						if (body === false) {
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

export = MessageStream
