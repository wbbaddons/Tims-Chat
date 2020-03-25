/*
 * Copyright (c) 2010-2018 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2024-03-25
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

define([ 'WoltLabSuite/Core/Environment'
       , '../Ui'
       ], function (Environment, Ui) {
	"use strict";

	const DEPENDENCIES = [ 'UiAutoAway'
	                     , 'UiConnectionWarning'
	                     , 'UiInput'
	                     , 'UiInputAutocompleter'
	                     , 'UiMessageStream'
	                     , 'UiMessageActionDelete'
	                     , 'UiMobile'
	                     , 'UiNotification'
	                     , 'UiReadMarker'
	                     , 'UiSettings'
	                     , 'UiTopic'
	                     , 'UiUserActionDropdownHandler'
	                     , 'UiUserList'
	                     ]
	class Chat extends Ui {
		constructor(autoAway, connectionWarning, input, autocompleter, messageStream, messageActionDelete, mobile, notification, readMarker, settings, topic, userActionDropdownHandler, userList) {
			super()

			this.actionDropdownHandler = userActionDropdownHandler
			this.autoAway              = autoAway
			this.autocompleter         = autocompleter
			this.connectionWarning     = connectionWarning
			this.input                 = input
			this.messageStream         = messageStream
			this.messageActionDelete   = messageActionDelete
			this.mobile                = mobile
			this.notification          = notification
			this.readMarker            = readMarker
			this.settings              = settings
			this.topic                 = topic
			this.userList              = userList
		}

		bootstrap() {
			this.actionDropdownHandler.bootstrap()
			this.autoAway.bootstrap()
			this.autocompleter.bootstrap()
			this.connectionWarning.bootstrap()
			this.input.bootstrap()
			this.messageStream.bootstrap()
			this.messageActionDelete.bootstrap()
			this.mobile.bootstrap()
			this.notification.bootstrap()
			this.readMarker.bootstrap()
			this.settings.bootstrap()
			this.topic.bootstrap()
			this.userList.bootstrap()
		}
	}
	Chat.DEPENDENCIES = DEPENDENCIES

	return Chat
});
