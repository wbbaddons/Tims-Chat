Tims Chat 3
===========

This is the main javascript file for [**Tims Chat**](https://github.com/wbbaddons/Tims-Chat). It handles
everything that happens in the GUI of **Tims Chat**.

	### Copyright Information  
	# @author	Tim Düsterhus  
	# @copyright	2010-2013 Tim Düsterhus  
	# @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>  
	# @package	be.bastelstu.chat  
	###

## Code
We start by setting up our environment by ensuring some sane values for both `$` and `window`,
enabling EMCAScript 5 strict mode and overwriting console to prepend the name of the class.

	(($, window) ->
		"use strict";
		
		console =
			log: (message) ->
				window.console.log "[be.bastelstu.Chat] #{message}"
			warn: (message) ->
				window.console.warn "[be.bastelstu.Chat] #{message}"
			error: (message) ->
				window.console.error "[be.bastelstu.Chat] #{message}"

Continue with defining the needed variables. All variables are local to our closure and will be
exposed by a function if necessary.

		isActive = true
		newMessageCount = 0
		scrollUpNotifications = off
		chatSession = Date.now()
		userList =
			current: {}
			allTime: {}
		currentRoom = {}
		fileUploaded = no
		
		errorVisible = false
		inputErrorHidingTimer = null

		lastMessage = null
		openChannel = 0
		
		remainingFailures = 3

		events =
			newMessage: $.Callbacks()
			userMenu: $.Callbacks()
			submit: $.Callbacks()

		pe =
			getMessages: null
			refreshRoomList: null
			fish: null

		loading = false

		autocomplete =
				offset: 0
				value: null
				caret: 0

		v =
			titleTemplate: null
			messageTemplate: null
			userTemplate: null
			config: null

Initialize **Tims Chat**. Bind needed DOM events and initialize data structures.

		initialized = false
		init = (roomID, config, titleTemplate, messageTemplate, userTemplate) ->
			return false if initialized
			initialized = true
			
			v.config = config
			v.titleTemplate = titleTemplate
			v.messageTemplate = messageTemplate
			v.userTemplate = userTemplate
			
			console.log 'Initializing'

When **Tims Chat** becomes focused mark the chat as active and remove the number of new messages from the title.

			$(window).focus ->
				document.title = v.titleTemplate.fetch currentRoom
				
				newMessageCount = 0
				isActive = true

When **Tims Chat** loses the focus mark the chat as inactive.

			$(window).blur -> isActive = false

Make the user leave the chat when **Tims Chat** is about to be unloaded.

			$(window).on 'beforeunload', ->
				return undefined if errorVisible
				
				new WCF.Action.Proxy
					autoSend: true
					data:
						actionName: 'leave'
						className: 'chat\\data\\room\\RoomAction'
					showLoadingOverlay: false
					async: false
					suppressErrors: true
				undefined

Insert the appropriate smiley code into the input when a smiley is clicked.

			$('#smilies').on 'click', 'img', -> insertText " #{$(@).attr('alt')} "

Handle private channel menu

			$('#privateChannelsMenu').on 'click', '.privateChannel', -> openPrivateChannel $(@).data 'privateChannelID'

Handle submitting the form. The message will be validated by some basic checks, passed to the `submit` eventlisteners
and afterwards sent to the server by an AJAX request.

			$('#timsChatForm').submit (event) ->
				do event.preventDefault
				
				text = do $('#timsChatInput').val().trim
				$('#timsChatInput').val('').focus().keyup()
				
				return false if text.length is 0
				
				text = "/whisper #{userList.allTime[openChannel].username}, #{text}" unless openChannel is 0
				
				# Free the fish!
				do freeTheFish if text.toLowerCase() is '/free the fish'
				
				text = do (text) ->
					obj =
						text: text
					events.submit.fire obj
					
					obj.text
				
				new WCF.Action.Proxy
					autoSend: true
					data:
						actionName: 'send'
						className: 'chat\\data\\message\\MessageAction'
						parameters:
							text: text
							enableSmilies: $('#timsChatSmilies').data 'status'
					showLoadingOverlay: false
					success: ->
						do hideInputError
						
						do getMessages
					failure: (data) ->
						return true unless (data?.returnValues?.errorType?) or (data?.message?)
						
						showInputError (data?.returnValues?.errorType) ? data.message
						
						false

Autocomplete a username when TAB is pressed. The name to autocomplete is based on the current caret position.
The the word the caret is in will be passed to `autocomplete` and replaced if a match was found.

			$('#timsChatInput').keydown (event) ->
				if event.keyCode is $.ui.keyCode.TAB
					do event.preventDefault
					input = $ @
					
					autocomplete.value ?= do input.val
					autocomplete.caret ?= do input.getCaret
					
					beforeCaret = autocomplete.value.substring 0, autocomplete.caret
					lastSpace = beforeCaret.lastIndexOf ' '
					beforeComplete = autocomplete.value.substring 0, lastSpace + 1
					toComplete = autocomplete.value.substring lastSpace + 1
					nextSpace = toComplete.indexOf ' '
					if nextSpace is -1
						afterComplete = '';
					else
						afterComplete = toComplete.substring nextSpace + 1
						toComplete = toComplete.substring 0, nextSpace
					
					return if toComplete.length is 0
					console.log "Autocompleting '#{toComplete}'"
					
					if beforeComplete is '' and (toComplete.substring 0, 1) is '/'
						regex = new RegExp "^#{WCF.String.escapeRegExp toComplete.substring 1}", "i"
						commands = (command for command in v.config.installedCommands when regex.test command)
						
						toComplete = '/' + commands[autocomplete.offset++ % commands.length] + ' ' if commands.length isnt 0
					else
						regex = new RegExp "^#{WCF.String.escapeRegExp toComplete}", "i"
						
						users = [ ]
						for userID, user of userList.current
							users.push user.username if regex.test user.username
						
						toComplete = users[autocomplete.offset++ % users.length] + ', ' if users.length isnt 0
					
					input.val "#{beforeComplete}#{toComplete}#{afterComplete}"
					input.setCaret (beforeComplete + toComplete).length

Reset autocompleter to default status, when a key is pressed that is not TAB.

				else
					do $('#timsChatInput').click

Reset autocompleter to default status, when the input is `click`ed, as the position of the caret may have changed.

			$('#timsChatInput').click ->
				autocomplete =
					offset: 0
					value: null
					caret: null

Refresh the room list when the associated button is `click`ed.

			$('#timsChatRoomList button').click -> do refreshRoomList

Clear the chat by removing every single message once the clear button is `clicked`.

			$('#timsChatClear').click (event) ->
				do event.preventDefault
				do $('.timsChatMessageContainer.active .timsChatMessage').remove
				$('.timsChatMessageContainer.active').scrollTop $('.timsChatMessageContainer.active').prop 'scrollHeight'

Handle toggling of the toggleable buttons.

			$('.timsChatToggle').click (event) ->
				element = $ @
				if element.data('status') is 1
					element.data 'status', 0
					element.removeClass 'active'
					element.attr 'title', element.data 'enableMessage'
				else
					element.data 'status', 1
					element.addClass 'active'
					element.attr 'title', element.data 'disableMessage'
					
				do $('#timsChatInput').focus

Mark smilies as disabled when they are disabled.

			$('#timsChatSmilies').click (event) ->
				if $(@).data 'status'
					$('#smilies').removeClass 'disabled'
				else
					$('#smilies').addClass 'disabled'

Toggle fullscreen mode.

			$('#timsChatFullscreen').click (event) ->
				# Force dropdowns to reorientate
				$('.dropdownMenu').data 'orientationX', ''
				
				if $(@).data 'status'
					$('html').addClass 'fullscreen'
				else
					$('html').removeClass 'fullscreen'

Toggle checkboxes.

			$('#timsChatMark').click (event) ->
				if $(@).data 'status'
					$('.timsChatMessageContainer').addClass 'markEnabled'
				else
					$('.timsChatMessageContainer').removeClass 'markEnabled'

Hide topic container.

			$('.jsTopicCloser').on 'click', ->
				if $('.timsChatMessageContainer.active').data('userID') is 0
					$('#timsChatTopic').addClass 'hidden'
				else
					closePrivateChannel $('.timsChatMessageContainer.active').data('userID')
					
Visibly mark the message once the associated checkbox is checked.

			$(document).on 'click', '.timsChatMessage :checkbox', (event) ->
				if $(@).is ':checked'
					$(@).parents('.timsChatMessage').addClass 'jsMarked'
				else
					$(@).parents('.timsChatMessage').removeClass 'jsMarked'

Scroll down when autoscroll is being activated.

			$('#timsChatAutoscroll').click (event) ->
				if $('#timsChatAutoscroll').data 'status'
					$('.timsChatMessageContainer.active').scrollTop $('.timsChatMessageContainer.active').prop 'scrollHeight'
			
			$('.timsChatMessageContainer.active').on 'scroll', (event) ->
				element = $ @
				scrollTop = element.scrollTop()
				scrollHeight = element.prop 'scrollHeight'
				height = element.height()
				
				if scrollTop < scrollHeight - height - 25
					if $('#timsChatAutoscroll').data('status') is 1
						scrollUpNotifications = on
						do $('#timsChatAutoscroll').click
						
				if scrollTop > scrollHeight - height - 10
					if $('#timsChatAutoscroll').data('status') is 0
						scrollUpNotifications = off
						$(@).removeClass 'notification'
						do $('#timsChatAutoscroll').click

Enable duplicate tab detection.

			try
				window.localStorage.setItem 'be.bastelstu.chat.session', chatSession
				$(window).on 'storage', (event) ->
					if event.originalEvent.key is 'be.bastelstu.chat.session'
						showError WCF.Language.get 'chat.error.duplicateTab' unless parseInt(event.originalEvent.newValue) is chatSession

Ask for permissions to use Desktop notifications when notifications are activated.

			if window.Notification?
				$('#timsChatNotify').click (event) ->
					return unless $(@).data 'status'
					unless window.Notification.permission is 'granted'
						window.Notification.requestPermission (permission) ->
							window.Notification.permission ?= permission
			
			events.newMessage.add notify

Initialize the `PeriodicalExecuter`s

			pe.refreshRoomList = new WCF.PeriodicalExecuter refreshRoomList, 60e3
			pe.getMessages = new WCF.PeriodicalExecuter getMessages, v.config.reloadTime * 1e3

Initialize the [**nodePush**](https://github.com/wbbaddons/nodePush) integration of **Tims Chat**. Once
the browser is connected to **nodePush** periodic message loading will be disabled and **Tims Chat** will
load messages if the appropriate event arrives.

			do ->
				be.bastelstu.wcf.nodePush.onConnect ->
						console.log 'Disabling periodic loading'
						do pe.getMessages.stop
						
				be.bastelstu.wcf.nodePush.onDisconnect ->
						console.log 'Enabling periodic loading'
						do getMessages
						pe.getMessages = new WCF.PeriodicalExecuter getMessages, v.config.reloadTime * 1e3
						
				be.bastelstu.wcf.nodePush.onMessage 'be.bastelstu.chat.newMessage', getMessages
				be.bastelstu.wcf.nodePush.onMessage 'be.bastelstu.wcf.nodePush.tick60', getMessages

Finished! Enable the input now and join the chat.

			join roomID
			do $('#timsChatInput').enable().jCounter().focus
			
			console.log "Finished initializing"
			
			true

Shows an error message below the input.

		showInputError = (message) ->
			$('#timsChatInputContainer').addClass('formError').find('.innerError').show().html message
			
			clearTimeout inputErrorHidingTimer if inputErrorHidingTimer?
			inputErrorHidingTimer = setTimeout ->
				do hideInputError
			, 5e3

Hides the error message below the input.

		hideInputError = ->
			clearTimeout inputErrorHidingTimer if inputErrorHidingTimer?
			inputErrorHidingTimer = null
			
			do $('#timsChatInputContainer').removeClass('formError').find('.innerError').hide

Free the fish.

		freeTheFish = ->
			return if $.wcfIsset 'fish'
			console.warn 'Freeing the fish'
			fish = $ """<div id="fish">#{WCF.String.escapeHTML('><((((\u00B0>')}</div>"""
			
			fish.css
				position: 'fixed'
				top: '50%'
				left: '50%'
				color: 'black'
				textShadow: '1px 1px white'
				zIndex: 9999
			
			fish.appendTo $ 'body'
			pe.fish = new WCF.PeriodicalExecuter ->
				left = Math.random() * 100 - 50
				top = Math.random() * 100 - 50
				fish = $ '#fish'
				
				left *= -1 unless fish.width() < (fish.position().left + left) < ($(window).width() - fish.width())
				top *= -1 unless fish.height() < (fish.position().top + top) < ($(window).height() - fish.height())
				
				if left > 0
					fish.text '><((((\u00B0>' if left > 0
				else if left < 0
					fish.text '<\u00B0))))><'
				
				fish.animate
					top: (fish.position().top + top)
					left: (fish.position().left + left)
				, 1e3
			, 1.5e3

Fetch new messages from the server and pass them to `handleMessages`. The userlist will be passed to `handleUsers`.
`remainingFailures` will be decreased on failure and message loading will be entirely disabled once it reaches zero.

		getMessages = ->
			$.ajax v.config.messageURL,
				dataType: 'json'
				type: 'POST'
				success: (data) ->
					remainingFailures = 3
					handleMessages data.messages
					handleUsers data.users
					WCF.DOMNodeInsertedHandler.execute()
				error: ->
					console.error "Message loading failed, #{--remainingFailures} remaining"
					if remainingFailures <= 0
						do freeTheFish
						console.error 'To many failures, aborting'
						
						showError WCF.Language.get 'chat.error.onMessageLoad'
				complete: ->
					loading = false

Prevent loading messages in parallel.

				beforeSend: ->
					return false if loading
					
					loading = true

Insert the given messages into the chat stream.

		handleMessages = (messages) ->
			$('.timsChatMessageContainer.active').trigger 'scroll'
			
			for message in messages
				message.isInPrivateChannel = (String(message.type) is v.config.messageTypes.WHISPER) and ($.wcfIsset("timsChatMessageContainer#{message.receiver}") or $.wcfIsset("timsChatMessageContainer#{message.sender}"))
				
				events.newMessage.fire message
				
				createNewMessage = yes
				if  $('.timsChatMessage:last-child .text').is('ul') and lastMessage isnt null and lastMessage.type in [ 0, 7 ]
					if lastMessage.type is message.type and lastMessage.sender is message.sender and lastMessage.receiver is message.receiver and lastMessage.isInPrivateChannel is message.isInPrivateChannel
						createNewMessage = no
				
				if createNewMessage
					message.isFollowUp = no
					output = v.messageTemplate.fetch
						message: message
						messageTypes: v.config.messageTypes
					
					li = $ '<li></li>'
					li.addClass 'timsChatMessage'
					li.addClass "timsChatMessage#{message.type}"
					li.addClass "user#{message.sender}"
					li.addClass 'ownMessage' if message.sender is WCF.User.userID
					li.append output
					
					if message.isInPrivateChannel and message.sender is WCF.User.userID
						li.appendTo $ "#timsChatMessageContainer#{message.receiver} > ul"
					else if message.isInPrivateChannel
						li.appendTo $ "#timsChatMessageContainer#{message.sender} > ul"
					else
						li.appendTo $ '#timsChatMessageContainer0 > ul'
				else
					message.isFollowUp = yes
					output = v.messageTemplate.fetch
						message: message
						messageTypes: v.config.messageTypes
					
					if message.isInPrivateChannel and message.sender is WCF.User.userID
						messageContainerID = message.receiver
					else if message.isInPrivateChannel
						messageContainerID = message.sender
					else
						messageContainerID = 0

					$("#timsChatMessageContainer#{messageContainerID} .timsChatMessage:last-child .text").append $(output).find('.text li:last-child')
				
				lastMessage = message
			$('.timsChatMessageContainer.active').scrollTop $('.timsChatMessageContainer.active').prop('scrollHeight') if $('#timsChatAutoscroll').data('status') is 1

Rebuild the userlist based on the given `users`.

		handleUsers = (users) ->
			foundUsers = { }
			userList.current = { }

			for user in users
				do (user) ->
					userList.current[user.userID] = userList.allTime[user.userID] = user
					
					id = "timsChatUser#{user.userID}"

Move the user to the new position if he was found in the old list.

					if $.wcfIsset id
						console.log "Moving User: '#{user.username}'"
						element = $("##{id}").detach()
						
						if user.awayStatus?
							element.addClass 'away'
							element.attr 'title', user.awayStatus
						else
							element.removeClass 'away'
							element.removeAttr 'title'
							element.data 'tooltip', ''
						
						if user.suspended
							element.addClass 'suspended'
						else
							element.removeClass 'suspended'
						
						$('#timsChatUserList > ul').append element

Build HTML of the user and insert it into the list, if the users was not found in the chat before.

					else
						console.log "Inserting User: '#{user.username}'"
						li = $ '<li></li>'
						li.attr 'id', id
						li.addClass 'timsChatUser'
						li.addClass 'jsTooltip'
						li.addClass 'dropdown'
						li.addClass 'you' if user.userID is WCF.User.userID
						li.addClass 'suspended' if user.suspended
						if user.awayStatus?
							li.addClass 'away'
							li.attr 'title', user.awayStatus
						li.data 'username', user.username
						
						li.append v.userTemplate.fetch user
						
						menu = $ '<ul></ul>'
						unless user.userID is WCF.User.userID
							menu.append $("<li><a>#{WCF.Language.get('chat.general.query')}</a></li>").click -> openPrivateChannel user.userID
							menu.append $ "<li><a>#{WCF.Language.get('chat.general.kick')}</a></li>"
							menu.append $ "<li><a>#{WCF.Language.get('chat.general.ban')}</a></li>"
							menu.append $ """<li><a href="#{user.link}">#{WCF.Language.get('chat.general.profile')}</a></li>"""
						
						events.userMenu.fire user, menu
						
						if menu.find('li').length
							li.append menu
							menu.addClass 'dropdownMenu'
						
						li.appendTo $ '#timsChatUserList > ul'
					
					foundUsers[id] = true

Remove all users that left the chat.

			$('.timsChatUser').each ->
				unless foundUsers[$(@).attr('id')]?
					console.log "Removing User: '#{$(@).data('username')}'"
					WCF.Dropdown.removeDropdown $(@).attr 'id'
					do $(@).remove
					
			
			$('#toggleUsers .badge').text $('.timsChatUser').length

Insert the given `text` into the input. If `options.append` is true the given `text` will be appended, otherwise it will replaced
the existing text. If `options.submit` is true the message will be sent to the server afterwards.

		insertText = (text, options = { }) ->
			options = $.extend
				append: true
				submit: false
			, options
			
			text = $('#timsChatInput').val() + text if options.append
			$('#timsChatInput').val text
			do $('#timsChatInput').keyup
			
			if options.submit
				do $('#timsChatForm').submit
			else
				do $('#timsChatInput').focus

Send out notifications for the given `message`. The number of unread messages will be prepended to `document.title` and if available desktop notifications will be sent.

		notify = (message) ->
			if scrollUpNotifications
				$('.timsChatMessageContainer.active').addClass 'notification'
			
			if message.isInPrivateChannel
				if message.sender is WCF.User.userID
					privateChannelID = message.receiver
				else
					privateChannelID = message.sender
				
				if $('.timsChatMessageContainer.active').data('userID') isnt privateChannelID
					$("#privateChannel#{privateChannelID}").addClass 'notify'
			else if $('.timsChatMessageContainer.active').data('userID') isnt 0
				$("#privateChannel0").addClass 'notify'
				
			return if isActive or $('#timsChatNotify').data('status') is 0
			
			document.title = v.titleTemplate.fetch $.extend {}, currentRoom,
				newMessageCount: ++newMessageCount
			
			title = WCF.Language.get 'chat.general.notify.title'
			content = "#{message.username}#{message.separator} #{message.message}"
			
			if window.Notification?.permission is 'granted'
				do ->
					notification = new window.Notification title,
						body: content
						onclick: ->
							do notification.close
					setTimeout ->
						do notification.close
					, 5e3

Fetch the roomlist from the server and update it in the GUI.

		refreshRoomList = ->
			console.log 'Refreshing the roomlist'
			
			new WCF.Action.Proxy
				autoSend: true
				data:
					actionName: 'getRoomList'
					className: 'chat\\data\\room\\RoomAction'
				showLoadingOverlay: false
				suppressErrors: true
				success: (data) ->
					do $('.timsChatRoom').remove
					$('#toggleRooms .badge').text data.returnValues.length
					
					for room in data.returnValues
						li = $ '<li></li>'
						li.addClass 'active' if room.active
						$("""<a href="#{room.link}">#{room.title} <span class="badge">#{WCF.String.formatNumeric room.userCount}</span></a>""").addClass('timsChatRoom').data('roomID', room.roomID).appendTo li
						$('#timsChatRoomList ul').append li
					
					if window.history?.replaceState?
						$('.timsChatRoom').click (event) ->
							do event.preventDefault
							target = $ @
							
							window.history.replaceState {}, '', target.attr 'href'
							
							join target.data 'roomID'
							$('#timsChatRoomList .active').removeClass 'active'
							target.parent().addClass 'active'
					
					console.log "Found #{data.returnValues.length} rooms"

Shows an unrecoverable error with the given text.

		showError = (text) ->
			return if errorVisible
			errorVisible = true
			
			loading = true
			
			do pe.refreshRoomList.stop
			do pe.getMessages.stop
			
			errorDialog = $("""
				<div id="timsChatLoadingErrorDialog">
					<p>#{text}</p>
				</div>
			""").appendTo 'body'
			
			formSubmit = $("""<div class="formSubmit"></div>""").appendTo errorDialog
			
			reloadButton = $("""<button class="buttonPrimary">#{WCF.Language.get 'chat.error.reload'}</button>""").appendTo formSubmit
			reloadButton.on 'click', -> do window.location.reload
			
			$('#timsChatLoadingErrorDialog').wcfDialog
				closable: false
				title: WCF.Language.get 'wcf.global.error.title'

Joins a room.

		join = (roomID) ->
			loading = true
			new WCF.Action.Proxy
				autoSend: true
				data:
					actionName: 'join'
					className: 'chat\\data\\room\\RoomAction'
					parameters:
						roomID: roomID
				success: (data) ->
					loading = false
					
					$('#timsChatTopic').removeClass 'hidden'
					currentRoom = data.returnValues
					currentRoom.roomID = roomID
					
					$('#timsChatTopic > .topic').text currentRoom.topic
					if currentRoom.topic.trim() is ''
						$('#timsChatTopic').addClass 'empty'
					else
						$('#timsChatTopic').removeClass 'empty'
					
					$('.timsChatMessage').addClass 'unloaded'
					
					document.title = v.titleTemplate.fetch currentRoom
					handleMessages currentRoom.messages
					do getMessages
					do refreshRoomList
				failure: ->
					showError WCF.Language.get 'chat.error.join'

Open private channel

		openPrivateChannel = (userID) ->
			userID = parseInt userID
			
			console.log "Opening private channel #{userID}"
			
			unless $.wcfIsset "timsChatMessageContainer#{userID}"
				return unless userList.allTime[userID]?
				
				div = $ '<div>'
				div.attr 'id', "timsChatMessageContainer#{userID}"
				div.data 'userID', userID
				div.addClass 'timsChatMessageContainer'
				div.addClass 'marginTop'
				div.addClass 'container'
				div.wrapInner '<ul>'
				$('#timsChatMessageContainer0').after div
			
			$('.privateChannel').removeClass 'active'
			
			if userID isnt 0
				$('#timsChatTopic').removeClass 'hidden empty'
				$('#timsChatTopic > .topic').text WCF.Language.get 'chat.general.privateChannelTopic', {username: userList.allTime[userID].username}
				$('#timsChatTopic > .jsTopicCloser').attr 'title', WCF.Language.get 'chat.general.closePrivateChannel'
				
				unless $.wcfIsset "privateChannel#{userID}"
					li = $ '<li>'
					li.attr 'id', "privateChannel#{userID}"
					li.data 'privateChannelID', userID
					li.addClass 'privateChannel'
					
					span = $ '<span class="userAvatar framed" />'
					
					avatar = $ userList.allTime[userID].avatar[16]
					avatar.addClass 'jsTooltip'
					avatar.attr 'title', userList.allTime[userID].username
					avatar.wrap span
					li.append avatar.parent().addClass 'small'
					
					avatar = $ userList.allTime[userID].avatar[32]
					avatar.addClass 'jsTooltip'
					avatar.attr 'title', userList.allTime[userID].username
					avatar.wrap span
					li.append avatar.parent().addClass 'large'
					
					$('#privateChannelsMenu ul').append li
					
				$('#privateChannelsMenu').addClass 'shown'
			else
				$('#timsChatTopic > .topic').text currentRoom.topic
				$('#timsChatTopic > .jsTopicCloser').attr 'title', WCF.Language.get 'chat.general.closeTopic'
				if currentRoom.topic.trim() is ''
					$('#timsChatTopic').addClass 'empty'
				else
					$('#timsChatTopic').removeClass 'empty'
			
			do WCF.DOMNodeInsertedHandler.execute
			
			$('.timsChatMessageContainer').removeClass 'active'
			$("#timsChatMessageContainer#{userID}").addClass 'active'
			$("#privateChannel#{userID}").addClass('active').removeClass 'notify'
			openChannel = userID

Close private channel

		closePrivateChannel = (userID) ->
			unless userID is 0
				do $("#privateChannel#{userID}").remove
				do $("#timsChatMessageContainer#{userID}").remove
				
			if $('#privateChannelsMenu li').length <= 1
				$('#privateChannelsMenu').removeClass 'shown'
			
			openPrivateChannel 0

Bind the given callback to the given event.

		addListener = (event, callback) ->
			return false unless events[event]?
			events[event].add callback
			
			true

Remove the given callback from the given event.

		removeListener = (event, callback) ->
			return false unless events[event]?
			events[event].remove callback
			
			true
			
The following code handles attachment uploads

Enable attachment code if `WCF.Attachment.Upload` is defined

		if WCF?.Attachment?.Upload? and $('#timsChatUploadContainer').length
			Attachment = WCF.Attachment.Upload.extend
				fileUploaded: no
				
Initialize WCF.Attachment.Upload
See WCF.Attachment.Upload.init()

				init: ->
					@_super $('#timsChatUploadContainer'), $(false), 'be.bastelstu.chat.message', 0, 0, 0, 1, null
					unless @_supportsAJAXUpload
						$('#timsChatUploadDropdownMenu .uploadButton').click => do @_showOverlay
						
					label = $ '#timsChatUploadDropdownMenu li > span > label'
					parent = do label.parent
					
					css = parent.css ['padding-top', 'padding-right', 'padding-bottom', 'padding-left']
					
					label.css css
					label.css 'margin', "-#{css['padding-top']} -#{css['padding-right']} -#{css['padding-bottom']} -#{css['padding-left']}"
					$('#timsChatUpload').click ->
						$('#timsChatUpload > span.icon-ban-circle').removeClass('icon-ban-circle').addClass 'icon-paper-clip'
						do $('#timsChatUploadContainer .innerError').remove
						
Overwrite WCF.Attachment.Upload._createButton() to create the upload button as small button into a button group
					
				_createButton: ->
					if @_supportsAJAXUpload
						@_fileUpload = $ """<input id="timsChatUploadInput" type="file" name="#{@_name}" />"""
						@_fileUpload.change =>	do @_upload
						@_fileUpload.appendTo 'body'
						
				_removeButton: ->
					do @_fileUpload.remove
				
See WCF.Attachment.Upload._getParameters()
					
				_getParameters: ->
					@_tmpHash = do Math.random
					@_parentObjectID = currentRoom.roomID
					
					do @_super
					
				
				_upload: ->
					files = @_fileUpload.prop 'files'
					if files.length
						$('#timsChatUpload > span.icon').removeClass('icon-paper-clip icon-ban-circle').addClass('icon-spinner')
						do @_super
				
Create a message containing the uploaded attachment
				
				_insert: (event) ->
					objectID = $(event.currentTarget).data 'objectID'
					
					new WCF.Action.Proxy
						autoSend: true
						data:
							actionName: 'sendAttachment'
							className: 'chat\\data\\message\\MessageAction'
							parameters:
								objectID: objectID
								tmpHash: @_tmpHash
								parentObjectID: 1#@_parentObjectID
						showLoadingOverlay: false
						
						success: ->
							do $('#timsChatUploadDropdownMenu .jsDeleteButton').parent().remove
							do $('#timsChatUploadDropdownMenu .sendAttachmentButton').remove
							do $('#timsChatUploadDropdownMenu .uploadButton').show
							$('#timsChatUpload > span.icon').removeClass('icon-ok-sign').addClass 'icon-paper-clip'
							fileUploaded = no
							
						failure: (data) ->
							false
							
				_initFile: (file) ->
					li = $("""<li class="uploadProgress">
							<span>
								<progress max="100"></progress>
							</span>
						</li>"""
					).data('filename', file.name)
					
					$('#timsChatUploadDropdownMenu').append li
					do $('#timsChatUploadDropdownMenu .uploadButton').hide
					# validate file size
					if @_buttonSelector.data('maxSize') < file.size
						# remove progress bar
						do li.find('progress').remove
						
						# upload icon
						$('#timsChatUpload > span.icon-spinner').removeClass('icon-spinner').addClass 'icon-ban-circle'
						
						# error message
						$('#timsChatUploadContainer').append """<small class="innerError">#{WCF.Language.get('wcf.attachment.upload.error.tooLarge')}</small>"""
						
						do @_error
						li.addClass 'uploadFailed'
					li
					
				_validateLimit: ->
					innerError = @_buttonSelector.next 'small.innerError'
					
					if fileUploaded
						# reached limit
						unless innerError.length
							innerError = $('<small class="innerError" />').insertAfter '#timsChatUpload'
						
						innerError.html WCF.Language.get('wcf.attachment.upload.error.reachedLimit')
						innerError.css 'position', 'absolute'
						
						return false
						
					# remove previous errors
					do innerError.remove
					
					true
					
				_success: (uploadID, data) ->
					for li in @_uploadMatrix[uploadID]
						do li.find('progress').remove
						li.removeClass('uploadProgress').addClass 'sendAttachmentButton'
						
						li.find('span').addClass('box32').append """
							<div class="framed attachmentImageContainer">
								<span class="attachmentTinyThumbnail icon icon32 icon-paper-clip"></span>
							</div>
							<div class="containerHeaderline">
								<p></p>
								<small></small>
								<p>#{WCF.Language.get('wcf.global.button.submit')}</p>
							</div>"""
						
						li.click (event) => @_insert(event)
						
						filename = li.data 'filename'
						
						if data.returnValues and data.returnValues.attachments[filename]
							if data.returnValues.attachments[filename].tinyURL
								li.find('.box32 > div.attachmentImageContainer > .icon-paper-clip').replaceWith $("""<img src="#{data.returnValues.attachments[filename].tinyURL}'" alt="" class="attachmentTinyThumbnail" style="width: 32px; height: 32px;" />""")
								
							link = $ '<a href="" class="jsTooltip"></a>'
							link.attr {'href': data.returnValues.attachments[filename].url, 'title': filename}
							
							unless parseInt(data.returnValues.attachments[filename].isImage) is 0
								link.addClass('jsImageViewer')
								
								if !data.returnValues.attachments[filename].tinyURL
									li.find('.box32 > div.attachmentImageContainer > .icon-paper-clip').replaceWith $("""<img src="#{data.returnValues.attachments[filename].url}'" alt="" class="attachmentTinyThumbnail" style="width: 32px; height: 32px;" />""")
							
							li.find('.attachmentTinyThumbnail').wrap link
							li.find('small').append data.returnValues.attachments[filename].formattedFilesize
							
							li.data 'objectID', data.returnValues.attachments[filename].attachmentID
							
							deleteButton = $ """
								<li>
									<span class="jsDeleteButton" data-object-id="#{data.returnValues.attachments[filename].attachmentID}" data-confirm-message="#{WCF.Language.get('wcf.attachment.delete.sure')}">
										<span class="icon icon16 icon-remove pointer jsTooltip" />
										<span>#{WCF.Language.get('wcf.global.button.delete')}</span>
									</span>
								</li>"""
							li.parent().append deleteButton
						else
							$('#timsChatUpload .icon-spinner').removeClass('icon-spinner').addClass 'icon-ban-circle'
							
							if data.returnValues and data.returnValues.errors[filename]
								errorMessage = data.returnValues.errors[filename].errorType
							else
								errorMessage = 'uploadFailed'
							
							$('#timsChatUpload').addClass('uploadFailed').after """<small class="innerError">#{WCF.Language.get('wcf.attachment.upload.error.' + errorMessage)}</small>"""
							do $('#timsChatUploadDropdownMenu .sendAttachmentButton').remove
							do $('#timsChatUploadDropdownMenu .uploadButton').show
						
					do WCF.DOMNodeInsertedHandler.execute
					
					fileUploaded = yes
					$('#timsChatUpload > span.icon').removeClass('icon-spinner').addClass 'icon-ok-sign'
					do $('#timsChatUploadDropdownMenu .uploadProgress').remove
					do $('#timsChatUploadDropdownMenu .sendAttachmentButton').show
					
				_error: (jqXHR, textStatus, errorThrown) ->
					$('#timsChatUpload > .icon-spinner').removeClass('icon-spinner').addClass 'icon-ban-circle'
					$('#timsChatUpload').addClass('uploadFailed').after """<small class="innerError">#{WCF.Language.get('wcf.attachment.upload.error.uploadFailed')}</small>"""
					
					do $('#timsChatUploadDropdownMenu .uploadProgress').remove
					do $('#timsChatUploadDropdownMenu .uploadButton').show
					
			Action = {}
			Action.Delete = WCF.Action.Delete.extend
				triggerEffect: (objectIDs) ->
					for index in @_containers
						container = $ "##{index}"
						if WCF.inArray container.find(@_buttonSelector).data('objectID'), objectIDs
							self = @
							container.wcfBlindOut 'up', (event) ->
								parent = do $(@).parent
								do $(@).remove
								do parent.find('.sendAttachmentButton').remove
								do parent.find('.uploadButton').show
								$('#timsChatUpload > .icon-ok-sign').removeClass('icon-ok-sign').addClass 'icon-paper-clip'
								
								self._containers.splice(self._containers.indexOf $(@).wcfIdentify(), 1)
								self._didTriggerEffect($ @)
							fileUploaded = no
					return
And finally export the public methods and variables.

		Chat =
			init: init
			getMessages: getMessages
			refreshRoomList: refreshRoomList
			insertText: insertText
			freeTheFish: freeTheFish
			join: join
			listener:
				add: addListener
				remove: removeListener
		Chat.Attachment = Attachment if Attachment?
		Chat.Action = Action if Attachment?
		
		window.be ?= {}
		be.bastelstu ?= {}
		window.be.bastelstu.Chat = Chat
	)(jQuery, @)
