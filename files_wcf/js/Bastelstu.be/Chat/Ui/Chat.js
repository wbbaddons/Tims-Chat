/*
 * Copyright (c) 2010-2020 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2024-10-31
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

define([ '../Ui' ], function (Ui) {
	"use strict";

	const DEPENDENCIES = [ 'UiAttachmentUpload'
	                     , 'UiAutoAway'
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
		constructor(attachmentUpload, autoAway, connectionWarning, input, autocompleter, messageStream, messageActionDelete, mobile, notification, readMarker, settings, topic, userActionDropdownHandler, userList) {
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
			this.attachmentUpload      = attachmentUpload
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
			this.attachmentUpload.bootstrap()
		}
	}
	Chat.DEPENDENCIES = DEPENDENCIES

	return Chat
});
