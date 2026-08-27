/*
 * Copyright (c) 2010-2026 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2030-08-27
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

import { Color, Side } from "./Helper"

class Node {
	value: number
	#left: Node | undefined
	#right: Node | undefined
	parent: Node | undefined
	color: Color

	constructor(value: number) {
		this.value = value
		this.#left = undefined
		this.#right = undefined
		this.parent = undefined
		this.color = Color.Red
	}

	get left(): Node | undefined {
		return this.#left
	}

	set left(node: Node | undefined) {
		if (this.#left) {
			this.#left.parent = undefined
		}

		if (node !== undefined) {
			if (node.parent !== undefined) {
				if (node.isLeftChild) {
					node.parent.left = undefined
				} else if (node.isRightChild) {
					node.parent.right = undefined
				} else {
					throw new Error('Unreachable')
				}
			}
			node.parent = this
		}

		this.#left = node
	}

	get right(): Node | undefined {
		return this.#right
	}

	set right(node: Node | undefined) {
		if (this.#right) {
			this.#right.parent = undefined
		}

		if (node !== undefined) {
			if (node.parent !== undefined) {
				if (node.isLeftChild) {
					node.parent.left = undefined
				} else if (node.isRightChild) {
					node.parent.right = undefined
				} else {
					throw new Error('Unreachable')
				}
			}

			node.parent = this
		}
		this.#right = node
	}

	get isRoot(): boolean {
		return this.parent === undefined
	}

	get isLeaf(): boolean {
		return this.left === undefined && this.right === undefined
	}

	get isLeftChild(): boolean {
		if (this.parent === undefined) {
			return false
		}

		return this.parent.left === this
	}

	get isRightChild(): boolean {
		if (this.parent === undefined) {
			return false
		}

		return this.parent.right === this
	}

	get grandparent(): Node | undefined {
		if (this.parent === undefined) {
			return undefined
		}

		return this.parent.parent
	}

	get sibling(): Node | undefined {
		if (this.parent === undefined) {
			return undefined
		}

		if (this.isLeftChild) {
			return this.parent.right
		}

		return this.parent.left
	}

	get uncle(): Node | undefined {
		if (this.parent === undefined) {
			return undefined
		}

		return this.parent.sibling
	}

	search(value: number): [Side, Node] {
		if (value === this.value) {
			return [Side.Is, this]
		}

		if (value < this.value) {
			if (this.left !== undefined) {
				return this.left.search(value)
			}
			return [Side.Left, this]
		}

		if (value > this.value) {
			if (this.right !== undefined) {
				return this.right.search(value)
			}
			return [Side.Right, this]
		}

		throw new Error('Unreachable')
	}

	print(depth = 0): void {
		console.log(
			'  '.repeat(depth) +
				`${this.value}: ${this.color} (Parent: ${
					this.parent ? this.parent.value : '-'
				})`
		)
		if (this.left) {
			this.left.print(depth + 1)
		} else {
			console.log('  '.repeat(depth + 1) + '-')
		}

		if (this.right) {
			this.right.print(depth + 1)
		} else {
			console.log('  '.repeat(depth + 1) + '-')
		}
	}

	check() {
		if (this.left && this.left.value >= this.value) {
			throw new Error('Invalid' + this.value)
		}

		if (this.right && this.right.value <= this.value) {
			throw new Error('Invalid' + this.value)
		}

		if (
			this.color === Color.Red &&
			((this.left && this.left.color !== Color.Black) ||
				(this.right && this.right.color !== Color.Black))
		) {
			throw new Error('Invalid' + this.value)
		}

		let leftBlacks = 1
		if (this.left) {
			leftBlacks = this.left.check()
		}

		let rightBlacks = 1
		if (this.right) {
			rightBlacks = this.right.check()
		}

		if (leftBlacks !== rightBlacks) {
			throw new Error('Invalid' + this.value)
		}

		if (this.color === Color.Black) {
			return leftBlacks + 1
		}

		return leftBlacks
	}
}

export = Node
