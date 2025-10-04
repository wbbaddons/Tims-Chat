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

import { Color, Side } from './Helper'

import Node from './Node'

class Tree {
	private root: Node | undefined

	constructor() {
		this.root = undefined
	}

	search(value: number): [Side, Node] | undefined {
		if (this.root !== undefined) {
			return this.root.search(value)
		}

		return undefined
	}

	insert(value: number): [Side, number | undefined] {
		const node = new Node(value)
		if (this.root === undefined) {
			this.root = node
			this.#fix(node)

			return [Side.Right, undefined]
		}

		const search = this.search(value)!
		const [side, parent] = search

		switch (side) {
			case Side.Is: {
				return [side, parent.value]
			}
			case Side.Left: {
				parent.left = node
				this.#fix(node)

				return [side, parent.value]
			}
			case Side.Right: {
				parent.right = node
				this.#fix(node)

				return [side, parent.value]
			}
		}
	}

	#fix(N: Node): void {
		// Case 1:
		if (N.parent === undefined) {
			N.color = Color.Black

			return
		}
		// Case 2:
		if (N.parent.color === Color.Black) {
			return
		}

		// Case 3:
		const U = N.uncle
		if (U !== undefined && U.color === Color.Red) {
			N.parent.color = Color.Black
			U.color = Color.Black
			const G = N.grandparent!
			G.color = Color.Red
			this.#fix(G)

			return
		}

		// Case 4:
		if (N.isRightChild && N.parent.isLeftChild) {
			this.#rotateLeft(N.parent)
			N = N.left!
		} else if (N.isLeftChild && N.parent.isRightChild) {
			this.#rotateRight(N.parent)
			N = N.right!
		}

		// Case 5
		const G = N.grandparent!
		N.parent!.color = Color.Black
		G.color = Color.Red

		if (N.isLeftChild) {
			this.#rotateRight(G)
		} else {
			this.#rotateLeft(G)
		}
	}

	#rotateLeft(N: Node): void {
		if (N.right === undefined) {
			return
		}

		const right = N.right
		N.right = right.left

		if (N.parent === undefined) {
			this.root = right
		} else if (N.isLeftChild) {
			N.parent.left = right
		} else if (N.isRightChild) {
			N.parent.right = right
		}

		right.left = N
	}

	#rotateRight(N: Node): void {
		if (N.left === undefined) {
			return
		}

		const left = N.left
		N.left = left.right

		if (N.parent === undefined) {
			this.root = left
		} else if (N.isLeftChild) {
			N.parent.left = left
		} else if (N.isRightChild) {
			N.parent.right = left
		}

		left.right = N
	}
}

export = Tree
