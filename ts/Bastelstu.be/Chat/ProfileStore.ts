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

import Ajax from 'Bastelstu.be/PromiseWrap/Ajax'
import LRU from './DataStructure/LRU'
import User from './User'
import CoreUser from 'WoltLabSuite/Core/User'

import type {
	DatabaseObjectActionResponse,
	RequestOptions,
} from 'WoltLabSuite/Core/Ajax/Data'

interface UserInformation extends ReturnType<User['toJSON']> {
	image16: string
	image24: string
	image32: string
	image48: string
	imageUrl: string
	link: string
	anchor: string
	userTitle: string
	userRankClass: string
	formattedUsername: string
	away: string | null
}

interface UserActionGetUsersByIDResponse extends DatabaseObjectActionResponse {
	returnValues: Record<string, UserInformation>
}

interface CachedUser extends User, UserInformation {}

/**
 * ProfileStore stores information about users.
 */
class ProfileStore {
	public static readonly DEPENDENCIES = []

	users: Map<number, { user: CachedUser; date: number }>
	processing: Map<number, Promise<void>>
	lastActivity: LRU<number>

	constructor() {
		this.users = new Map()
		this.processing = new Map()

		this.lastActivity = new LRU()
	}

	/**
	 * Ensures that information about the given userIDs are available
	 * in the store. The returned promise resolves once all the requests
	 * to fetch the data finished successfully.
	 */
	async ensureUsersByIDs(_userIDs: (number | string)[]): Promise<void> {
		// Dedup
		const userIDs: number[] = _userIDs
			.filter((value, index, self) => self.indexOf(value) === index)
			.map((userID: string) => parseInt(userID, 10))

		const missing: number[] = []
		const promises: Promise<void>[] = []
		userIDs.forEach((userID) => {
			if (this.isRecent(userID)) {
				return
			}

			if (this.processing.has(userID)) {
				promises.push(this.processing.get(userID)!)
				return
			}

			missing.push(userID)
		})

		if (missing.length > 0) {
			const payload = {
				actionName: 'getUsersByID',
				parameters: { userIDs: missing },
			}
			const request = (async (_) => {
				try {
					const response = (await Ajax.api(
						this,
						payload
					)) as UserActionGetUsersByIDResponse

					Object.entries(response.returnValues).forEach(([userIdStr, user]) => {
						const userID = parseInt(userIdStr, 10)
						const data = {
							user: new User(user) as CachedUser,
							date: Date.now(),
						}
						this.users.set(userID, data)
						this.processing.delete(userID)
					})

					return
				} catch (err) {
					missing.forEach((userID) => this.processing.delete(userID))

					throw err
				}
			})()

			missing.forEach((userID) => this.processing.set(userID, request))
			promises.push(request)
		}

		await Promise.all(promises)
	}

	/**
	 * Returns information about the given userIDs.
	 */
	async getUsersByIDs(userIDs: number[]): Promise<Map<number, CachedUser>> {
		await this.ensureUsersByIDs(userIDs)

		return new Map(userIDs.map((userID) => [userID, this.get(userID)!]))
	}

	/**
	 * Returns information about the currently logged in user.
	 */
	getSelf(): CachedUser {
		const self = this.get(CoreUser.userId)

		if (self == null) {
			throw new Error('Unreachable')
		}

		return self
	}

	/**
	 * Returns information about the given userID.
	 */
	get(userID: number): CachedUser | undefined {
		const user = this.users.get(userID)

		if (user != null) {
			return user.user
		}

		return user
	}

	/**
	 * Returns whether information about the given userID are known.
	 */
	has(userID: number): boolean {
		return this.users.has(userID)
	}

	/**
	 * Forces an update of the information about the given userID.
	 */
	expire(userID: number) {
		if (!this.users.has(userID)) {
			return
		}

		this.users.get(userID)!.date = 0
	}

	/**
	 * Returns whether the information about the given userID are recent.
	 */
	isRecent(userID: number): boolean {
		const user = this.users.get(userID)

		if (user != null) {
			return user.date > Date.now() - 5 * 60e3
		}

		return false
	}

	/**
	 * Returns the stored information.
	 */
	values(): CachedUser[] {
		return Array.from(this.users.values()).map((item) => item.user)
	}

	pushLastActivity(userID: number) {
		if (!userID) {
			return
		}

		this.lastActivity.add(userID)
	}

	*getLastActivity(): Generator<number, void, void> {
		yield* this.lastActivity
	}

	_ajaxSetup(): RequestOptions {
		return {
			silent: true,
			ignoreError: true,
			data: { className: 'chat\\data\\user\\UserAction' },
		}
	}
}

export = ProfileStore
