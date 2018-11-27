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

define([ 'Bastelstu.be/PromiseWrap/Ajax'
       , './DataStructure/LRU'
       , './User'
       , 'WoltLabSuite/Core/User'
       ], function (Ajax, LRU, User, CoreUser) {
	"use strict";

	const DEPENDENCIES = [ ]
	/**
	 * ProfileStore stores information about users.
	 */
	class ProfileStore {
		constructor() {
			this.users = new Map()
			this.processing = new Map()

			this.lastActivity = new LRU()
		}

		/**
		 * Ensures that information about the given userIDs are available
		 * in the store. The returned promise resolves once all the requests
		 * to fetch the data finished successfully.
		 *
		 * @param   {number[]} userIDs
		 * @returns {Promise}
		 */
		async ensureUsersByIDs(userIDs) {
			// Dedup
			userIDs = userIDs.filter((value, index, self) => self.indexOf(value) === index)
			                 .map(userID => parseInt(userID, 10))

			const missing = [ ]
			const promises = [ ]
			userIDs.forEach((function (userID) {
				if (this.isRecent(userID)) return
				if (this.processing.has(userID)) {
					promises.push(this.processing.get(userID))
					return
				}
				missing.push(userID)
			}).bind(this))

			if (missing.length > 0) {
				const payload = { actionName: 'getUsersByID'
				                , parameters: { userIDs: missing }
				                }
				const request = (async _ => {
					try {
						const response = await Ajax.api(this, payload)
						return Object.entries(response.returnValues).forEach(([ userID, user ]) => {
							userID = parseInt(userID, 10)
							const data = { user: new User(user)
							             , date: Date.now()
							             }
							this.users.set(userID, data)
							this.processing.delete(userID)
						})
					}
					catch (err) {
						missing.forEach(userID => this.processing.delete(userID))

						throw err
					}
				})()

				missing.forEach(userID => this.processing.set(userID, request))
				promises.push(request)
			}

			await Promise.all(promises)
		}

		/**
		 * Returns information about the given userIDs.
		 *
		 * @param   {number[]} userIDs
		 * @returns {Promise}
		 */
		async getUsersByIDs(userIDs) {
			await this.ensureUsersByIDs(userIDs)

			return new Map(userIDs.map(userID => [ userID, this.get(userID) ]))
		}

		/**
		 * Returns information about the currently logged in user.
		 *
		 * @returns {Promise}
		 */
		getSelf() {
			const self = this.get(CoreUser.userId)
			if (self == null) {
				throw new Error('Unreachable')
			}

			return self
		}

		/**
		 * Returns information about the given userID.
		 *
		 * @param   {number} userID
		 * @returns {?User} null if no information are known
		 */
		get(userID) {
			const user = this.users.get(userID)

			if (user != null) {
				return user.user
			}

			return user
		}

		/**
		 * Returns whether information about the given userID are known.
		 *
		 * @param   {number} userID
		 * @returns {boolean}
		 */
		has(userID) {
			return this.users.has(userID)
		}

		/**
		 * Forces an update of the information about the given userID.
		 *
		 * @param {number} userID
		 */
		expire(userID) {
			if (!this.users.has(userID)) return

			this.users.get(userID).date = 0
		}

		/**
		 * Returns whether the information about the given userID are recent.
		 *
		 * @param   {number} userID
		 * @returns {boolean}
		 */
		isRecent(userID) {
			const user = this.users.get(userID)

			if (user != null) {
				return user.date > (Date.now() - (5 * 60e3))
			}

			return false
		}

		/**
		 * Returns the stored information.
		 *
		 * @returns {User[]}
		 */
		values() {
			return Array.from(this.users.values()).map(item => item.user)
		}

		pushLastActivity(userID) {
			if (!userID) return

			this.lastActivity.add(userID)
		}

		* getLastActivity() {
			yield * this.lastActivity
		}

		_ajaxSetup() {
			return { silent: true
			       , ignoreError: true
			       , data: { className: 'chat\\data\\user\\UserAction' }
			       }
		}
	}
	ProfileStore.DEPENDENCIES = DEPENDENCIES

	return ProfileStore
});
