/*
 * Copyright (c) 2010-2018 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2022-11-27
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

define([ './Node' ], function (Node) {
	"use strict";

	class Tree {
		constructor() {
			this.root = undefined
		}

		search(value) {
			if (this.root !== undefined) return this.root.search(value)
			return undefined
		}

		insert(value) {
			const node = new Node(value)
			if (this.root === undefined) {
				this.root = node
				this.fix(node)
				return [ 'RIGHT', undefined ]
			}

			const search = this.search(value)
			const [ side, parent ] = search

			if (side === 'IS') return [ side, parent.value ]
			if (side === 'LEFT') {
				parent.left = node
				this.fix(node)
				return [ side, parent.value ]
			}
			if (side === 'RIGHT') {
				parent.right = node
				this.fix(node)
				return [ side, parent.value ]
			}
			throw new Error('Unreachable')
		}

		fix(N) {
			// Case 1:
			if (N.parent === undefined) {
				N.color = 'BLACK'
				return
			}
			// Case 2:
			if (N.parent.color === 'BLACK') {
				return
			}

			// Case 3:
			const U = N.uncle
			if (U !== undefined && U.color === 'RED') {
				N.parent.color = 'BLACK'
				U.color = 'BLACK'
				const G = N.grandparent
				G.color = 'RED'
				this.fix(G)
				return
			}
			// Case 4:
			if (N.isRightChild && N.parent.isLeftChild) {
				this.rotateLeft(N.parent)
				N = N.left
			}
			else if (N.isLeftChild && N.parent.isRightChild) {
				this.rotateRight(N.parent)
				N = N.right
			}

			// Case 5
			const G = N.grandparent
			N.parent.color = 'BLACK'
			G.color = 'RED'
			if (N.isLeftChild) {
				this.rotateRight(G)
			}
			else {
				this.rotateLeft(G)
			}
		}

		rotateLeft(N) {
			if (N.right === undefined) return

			const right = N.right
			N.right = right.left
			if (N.parent === undefined) {
				this.root = right
			}
			else if (N.isLeftChild) {
				N.parent.left = right
			}
			else if (N.isRightChild) {
				N.parent.right = right
			}

			right.left = N
		}

		rotateRight(N) {
			if (N.left === undefined) return

			const left = N.left
			N.left = left.right
			if (N.parent === undefined) {
				this.root = left
			}
			else if (N.isLeftChild) {
				N.parent.left = left
			}
			else if (N.isRightChild) {
				N.parent.right = left
			}
			left.right = N
		}
	}

	return Tree
});
