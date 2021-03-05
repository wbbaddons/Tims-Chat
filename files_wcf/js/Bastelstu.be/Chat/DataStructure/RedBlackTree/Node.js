/*
 * Copyright (c) 2010-2021 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2025-03-05
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

define([], function () {
	'use strict'

	class Node {
		constructor(value) {
			this.value = value
			this._left = undefined
			this._right = undefined
			this.parent = undefined
			this.color = 'RED'
		}

		get left() {
			return this._left
		}

		set left(node) {
			if (this._left) this._left.parent = undefined
			if (node !== undefined) {
				if (node.parent !== undefined) {
					if (node.isLeftChild) node.parent.left = undefined
					else if (node.isRightChild) node.parent.right = undefined
					else throw new Error('Unreachable')
				}
				node.parent = this
			}

			this._left = node
		}

		get right() {
			return this._right
		}

		set right(node) {
			if (this._right) this._right.parent = undefined
			if (node !== undefined) {
				if (node.parent !== undefined) {
					if (node.isLeftChild) node.parent.left = undefined
					else if (node.isRightChild) node.parent.right = undefined
					else throw new Error('Unreachable')
				}
				node.parent = this
			}
			this._right = node
		}

		get isRoot() {
			return this.parent === undefined
		}

		get isLeaf() {
			return this.left === undefined && this.right === undefined
		}

		get isLeftChild() {
			if (this.parent === undefined) return false
			return this.parent.left === this
		}

		get isRightChild() {
			if (this.parent === undefined) return false
			return this.parent.right === this
		}

		get grandparent() {
			if (this.parent === undefined) return undefined
			return this.parent.parent
		}

		get sibling() {
			if (this.parent === undefined) return undefined
			if (this.isLeftChild) return this.parent.right
			return this.parent.left
		}

		get uncle() {
			if (this.parent === undefined) return undefined
			return this.parent.sibling
		}

		search(value) {
			if (value === this.value) return ['IS', this]
			if (value < this.value) {
				if (this.left !== undefined) return this.left.search(value)
				return ['LEFT', this]
			}
			if (value > this.value) {
				if (this.right !== undefined) return this.right.search(value)
				return ['RIGHT', this]
			}
			throw new Error('Unreachable')
		}

		print(depth = 0) {
			console.log(
				'  '.repeat(depth) +
					`${this.value}: ${this.color} (Parent: ${
						this.parent ? this.parent.value : '-'
					})`
			)
			if (this.left) this.left.print(depth + 1)
			else console.log('  '.repeat(depth + 1) + '-')
			if (this.right) this.right.print(depth + 1)
			else console.log('  '.repeat(depth + 1) + '-')
		}

		check() {
			if (this.left && this.left.value >= this.value)
				throw new Error('Invalid' + this.value)
			if (this.right && this.right.value <= this.value)
				throw new Error('Invalid' + this.value)
			if (
				this.color === 'RED' &&
				((this.left && this.left.color !== 'BLACK') ||
					(this.right && this.right.color !== 'BLACK'))
			)
				throw new Error('Invalid' + this.value)

			let leftBlacks = 1,
				rightBlacks = 1
			if (this.left) {
				leftBlacks = this.left.check()
			}
			if (this.right) {
				rightBlacks = this.right.check()
			}
			if (leftBlacks !== rightBlacks) throw new Error('Invalid' + this.value)

			if (this.color === 'BLACK') return leftBlacks + 1
			return leftBlacks
		}
	}

	return Node
})
