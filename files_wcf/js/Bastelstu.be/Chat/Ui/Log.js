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

define(['../Ui'], function (Ui) {
	'use strict'

	const DEPENDENCIES = ['UiMessageStream', 'UiMessageActionDelete']
	class Log extends Ui {
		constructor(messageStream, messageActionDelete) {
			super()

			this.messageStream = messageStream
			this.messageActionDelete = messageActionDelete
		}

		bootstrap() {
			this.messageStream.bootstrap()
			this.messageStream.enableAutoscroll = false
			this.messageActionDelete.bootstrap()
		}
	}
	Log.DEPENDENCIES = DEPENDENCIES

	return Log
})
