/*
 * Copyright (c) 2010-2025 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2029-04-27
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

import type { ConfirmationOptions as CoreConfirmationOptions } from 'WoltLabSuite/Core/Ui/Confirmation'

type ConfirmationOptions = Omit<CoreConfirmationOptions, 'confirm' | 'cancel'>

declare class Confirmation {
	static show(options: ConfirmationOptions): Promise<void>
}

export = Confirmation
