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

import type {
	AjaxCallbackObject as CoreAjaxCallbackObject,
	CallbackSuccess,
	// CallbackFailure,
	RequestData,
	RequestOptions as CoreRequestOptions,
} from 'WoltLabSuite/Core/Ajax/Data'

type AjaxCallbackObject = Omit<
	CoreAjaxCallbackObject,
	'_ajaxFailure' | '_ajaxSuccess'
>
type RequestOptions = Omit<CoreRequestOptions, 'success' | 'failure'>
type Response = Parameters<CallbackSuccess>[0]
// type Error = Parameters<CallbackFailure>[0]

declare class Ajax {
	static api(
		callbackObject: AjaxCallbackObject,
		data?: RequestData
	): Promise<Response>
	static apiOnce(options: RequestOptions): void
}

export = Ajax
