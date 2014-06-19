Tims Chat 3
===========

This is the main javascript file for [**Tims Chat**](https://github.com/wbbaddons/Tims-Chat). It handles
everything that happens in the GUI of **Tims Chat**.

	### Copyright Information  
	# @author	Tim Düsterhus  
	# @copyright	2010-2014 Tim Düsterhus  
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
				window.console.log "[be.bastelstu.Chat] #{message}" unless production?
			warn: (message) ->
				window.console.warn "[be.bastelstu.Chat] #{message}" unless production?
			error: (message) ->
				window.console.error "[be.bastelstu.Chat] #{message}" unless production?
				
Continue with defining the needed variables. All variables are local to our closure and will be
exposed by a function if necessary.

		isActive = true
		newMessageCount = 0
		scrollUpNotifications = off
		chatSession = Date.now()
		
		userList =
			current: {}
			allTime: {}
			
		roomList =
			active: {}
			available: {}
			
		hiddenTopics = {}
		hidePrivateChannelTopic = no
		
		isJoining = no
		fileUploaded = no
		errorVisible = false
		inputErrorHidingTimer = null
		lastMessage = null
		openChannel = 0
		messageContainerSize = 0
		userListSize = 0
		remainingFailures = 3
		overlaySmileyList = null
		markedMessages = {}
		
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
		init = (roomID, config, titleTemplate, messageTemplate, userTemplate, userMenuTemplate) ->
			return false if initialized
			initialized = true
			
			userListSize = $('#timsChatUserList').height()
			
			v.config = config
			v.titleTemplate = titleTemplate
			v.messageTemplate = messageTemplate
			v.userTemplate = userTemplate
			v.userMenuTemplate = userMenuTemplate
			
			console.log 'Initializing'

When **Tims Chat** becomes focused mark the chat as active and remove the number of new messages from the title.

			$(window).focus ->
				document.title = v.titleTemplate.fetch(roomList.active) if roomList.active?.title? and roomList.active.topic.trim() isnt ''
				
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
				
			$(window).resize ->
				if $('html').hasClass 'fullscreen'
					do ->
						verticalContentPadding = $('#content').innerHeight() - $('#content').height()
						verticalSizeOfContentElements = do ->
							height = 0
							$('#content > *:visible').each (k, v) -> height += $(v).outerHeight()
							height
							
						return if verticalSizeOfContentElements is 0
						
						freeSpace = $('body').height() - verticalContentPadding - verticalSizeOfContentElements
						
						$('.timsChatMessageContainer').height $('.timsChatMessageContainer').height() + freeSpace
						
					do ->
						verticalSidebarPadding = $('.sidebar').innerHeight() - $('.sidebar').height()
						verticalUserListContainerPadding = $('#timsChatUserListContainer').innerHeight() - $('#timsChatUserListContainer').height()
						sidebarHeight = $('.sidebar > div').height()
						
						freeSpace = $('body').height() - verticalSidebarPadding - verticalUserListContainerPadding - sidebarHeight
						$('#timsChatUserList').height $('#timsChatUserList').height() + freeSpace
						
				if $('#timsChatAutoscroll').data 'status'
					$('.timsChatMessageContainer.active').scrollTop $('.timsChatMessageContainer.active').prop 'scrollHeight'
					
			$('.mobileSidebarToggleButton').on 'click', ->
				do $(window).resize
				
Insert the appropriate smiley code into the input when a smiley is clicked.

			$('#smilies').on 'click', 'img', -> insertText " #{$(@).attr('alt')} "

Copy the first loaded category of smilies so it won't get detached by wcfDialog

			overlaySmileyList = $('<ul class="smileyList">').append $('#smilies .smileyList').clone().children()

Add click event to smilies in the overlay

			overlaySmileyList.on 'click', 'img', ->
				insertText " #{$(@).attr('alt')} "
				overlaySmileyList.wcfDialog 'close'

Open the smiley wcfDialog

			$('#timsChatSmileyPopupButton').on 'click', ->
				overlaySmileyList.wcfDialog
					title: WCF.Language.get 'chat.global.smilies'
					
				overlaySmileyList.css
					'max-height': $(window).height() - overlaySmileyList.parent().siblings('.dialogTitlebar').outerHeight()
					'overflow': 'auto'
			
Handle private channel menu

			$('#timsChatMessageTabMenu > .tabMenu').on 'click', '.timsChatMessageTabMenuAnchor', ->
				openPrivateChannel $(@).data 'userID' 

Handle submitting the form. The message will be validated by some basic checks, passed to the `submit` eventlisteners
and afterwards sent to the server by an AJAX request.

			$('#timsChatForm').submit (event) ->
				do event.preventDefault
				
				text = do $('#timsChatInput').val().trim
				$('#timsChatInput').val('').focus().change()
				
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

Bind user menu functions

			$('#dropdownMenuContainer').on 'click', '.jsTimsChatUserMenuQuery', -> openPrivateChannel $(@).parents('ul').data 'userID'
			$('#dropdownMenuContainer').on 'click', '.jsTimsChatUserMenuCommand', ->
				command = "/#{$(@).data 'command'} #{userList.current[$(@).parents('ul').data 'userID'].username}, "
				return if $('#timsChatInput').val().match(new RegExp WCF.String.escapeRegExp("^#{command}"), 'i')
				
				insertText command, prepend: yes

Refresh the room list when the associated button is `click`ed.

			$('#timsChatRoomListReloadButton').click -> do refreshRoomList

Clear the chat by removing every single message once the clear button is `clicked`.

			$('#timsChatClear').click (event) ->
				do event.preventDefault
				clearChannel openChannel

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
					$('#smilies').removeClass 'invisible'
				else
					$('#smilies').addClass 'invisible'

Toggle fullscreen mode.

			$('#timsChatFullscreen').click (event) ->
				# Force dropdowns to reorientate
				$('.dropdownMenu').data 'orientationX', ''
				
				if $(@).data 'status'
					messageContainerSize = $('.timsChatMessageContainer').height()
					
					$('html').addClass 'fullscreen'
					do $(window).resize
				else
					$('.timsChatMessageContainer').height messageContainerSize
					$('#timsChatUserList').height userListSize
					$('html').removeClass 'fullscreen'
					do $(window).resize

Toggle checkboxes.

			$('#timsChatMark').click (event) ->
				if $(@).data 'status'
					$('.timsChatMessageContainer').addClass 'markEnabled'
				else
					$('.timsChatMessageContainer').removeClass 'markEnabled'

Hide topic container.

			$('#timsChatTopicCloser').on 'click', ->
				if openChannel is 0
					hiddenTopics[roomList.active.roomID] = true
				else
					hidePrivateChannelTopic = yes
					
				$('#timsChatTopic').addClass 'invisible'
				do $(window).resize
				
Close private channels
			
			$('#timsChatMessageTabMenu').on 'click', '.jsChannelCloser', -> closePrivateChannel $(@).parent().data 'userID'
			
Visibly mark the message once the associated checkbox is checked.

			$(document).on 'click', '.timsChatMessage .timsChatMessageMarker', (event) ->
				elem = $(event.target)
				parent = elem.parent()
				messageID = elem.attr('value')
				
				if elem.is ':checked'
					markedMessages[messageID] = messageID
					checked = true
					
					parent.addClass 'checked'
					parent.siblings().each (key, value) ->
						checked = $(value).find('.timsChatMessageMarker').is ':checked'
						
						checked
						
					if checked
						elem.parents('.timsChatMessage').addClass 'checked'
						elem.parents('.timsChatTextContainer').siblings('.timsChatMessageBlockMarker').prop 'checked', true
				else
					delete markedMessages[messageID]
					
					parent.removeClass 'checked'
					elem.parents('.timsChatMessage').removeClass 'checked'
					elem.parents('.timsChatTextContainer').siblings('.timsChatMessageBlockMarker').prop 'checked', false
					
			$(document).on 'click', '.timsChatMessageBlockMarker', (event) ->
				$(event.target).siblings('.timsChatTextContainer').children('li').each (key, value) ->
					elem = $(value).find '.timsChatMessageMarker'
					
					if $(event.target).is ':checked'
						do elem.click unless elem.is ':checked'
					else
						do elem.click if elem.is ':checked'

Scroll down when autoscroll is being activated.

			$('#timsChatAutoscroll').click (event) ->
				if $(@).data 'status'
					$('.timsChatMessageContainer.active').scrollTop $('.timsChatMessageContainer.active').prop 'scrollHeight'
					
					scrollUpNotifications = off
					$("#timsChatMessageTabMenu > .tabMenu > ul > li.ui-state-active").removeClass 'notify'
					$(".timsChatMessageContainer.active").removeClass 'notify'
				else
					scrollUpNotifications = on
			
Bind scroll event on predefined message containers
			
			$('.timsChatMessageContainer.active').on 'scroll', (event) ->
				do event.stopPropagation
				handleScroll event

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
						do pe.getMessages.resume
						
				be.bastelstu.wcf.nodePush.onMessage 'be.bastelstu.chat.newMessage', getMessages
				be.bastelstu.wcf.nodePush.onMessage 'be.bastelstu.wcf.nodePush.tick60', getMessages
				be.bastelstu.wcf.nodePush.onMessage 'be.bastelstu.chat.roomChange', refreshRoomList
				be.bastelstu.wcf.nodePush.onMessage 'be.bastelstu.chat.join', refreshRoomList
				be.bastelstu.wcf.nodePush.onMessage 'be.bastelstu.chat.leave', refreshRoomList

Switch to fullscreen mode on mobile devices

			do $('#timsChatFullscreen').click if WCF.System.Mobile.UX._enabled

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
			fish = $ """<div id="fish"><span></span></div>"""
			fish.direction = 'right'
			
			fish.css
				position: 'fixed'
				top: '50%'
				left: '50%'
				zIndex: 0x7FFFFFFF
				textShadow: '1px 1px rgb(0, 0, 0)'
				
			fish.appendTo $ 'body'
			
			fish.colors = ['78C5D6', '459ba8', '79C267', 'C5D647', 'F5D63D', 'F28C33', 'E868A2', 'BF62A6']
			fish.colorIndex = 0
			
			fish.texts =
				right: '><((((\u00B0>'
				left:  '<\u00B0))))><'
			fish.fishes = {}

Pre build fishes, this allows for faster animation

			$.each fish.texts, (key, value) ->
				fish.fishes[key] = []
				index = 0
				
				while index < value.length
					html = $ '<span/>'
					i = 0
					$(value.split '').each (key, value) ->
						$("<span>#{value}</span>").css
							color: '#' + fish.colors[(i++ + index) % fish.colors.length]
							textShadow: '1px 1px rgb(0, 0, 0)'
						.appendTo html
					fish.fishes[key][index++] = html
				return
			
			fish.find('> span').replaceWith fish.fishes[fish.direction][0]
			
			fish.updateRainbowText = (key, value) ->
				key = key || fish.direction
				return unless fish.fishes[key]? || not fish.texts[key]?
				value = value || fish.colorIndex++ % fish.texts[key].length
								
				fish.find('> span').replaceWith fish.fishes[key][value]
			
			fish.pePos = new WCF.PeriodicalExecuter ->
				loops = 0
				loop
					++loops
					
					left = Math.random() * 300 - 150
					top = Math.random() * 300 - 150
					
					if (fish.position().top + top) > 0 and (fish.position().left + left + fish.width()) < $(window).width() and (fish.position().top + top + fish.height()) < $(window).height() and (fish.position().left + left) > 0
						break
					else if loops is 10
						console.log 'Magicarp used Splash for the 10th time in a row - it fainted!'
						fish.css
							'top': '50%'
							'left': '50%'
						break
						
				if left > 0 and fish.text() isnt '><((((\u00B0>'
					fish.direction = 'right'
					fish.updateRainbowText null, fish.colorIndex % fish.texts.right.length
				else if left < 0 and fish.text() isnt '<\u00B0))))><'
					fish.direction = 'left'
					fish.updateRainbowText null, fish.colorIndex % fish.texts.left.length
				
				fish.animate
					top: (fish.position().top + top)
					left: (fish.position().left + left)
				, 1e3
			, 1.2e3
			
			fish.peColor = new WCF.PeriodicalExecuter ->
				do fish.updateRainbowText
			, .125e3

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
			for message in messages
				message.isInPrivateChannel = (message.type is v.config.messageTypes.WHISPER) and ($.wcfIsset("timsChatMessageContainer#{message.receiver}") or $.wcfIsset("timsChatMessageContainer#{message.sender}"))
				
				events.newMessage.fire message
				
				createNewMessage = yes
				if  $('.timsChatMessage:last-child .timsChatTextContainer').is('ul') and lastMessage isnt null and lastMessage.type in [ v.config.messageTypes.NORMAL, v.config.messageTypes.WHISPER ]
					if lastMessage.type is message.type and lastMessage.sender is message.sender and lastMessage.receiver is message.receiver and lastMessage.isInPrivateChannel is message.isInPrivateChannel
						createNewMessage = no
				
				if message.type is v.config.messageTypes.CLEAR
					createNewMessage = yes
					clearChannel 0
				
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

					$("#timsChatMessageContainer#{messageContainerID} .timsChatMessage:last-child .timsChatTextContainer").append $(output).find('.timsChatTextContainer li:last-child')
				
				lastMessage = message
			
			$('.timsChatMessageContainer.active').scrollTop $('.timsChatMessageContainer.active').prop('scrollHeight') if $('#timsChatAutoscroll').data('status') is 1

Handles scroll event of message containers

		handleScroll = (event) ->
			element = $ event.target
			
			if element.hasClass 'active'
				scrollTop = element.scrollTop()
				scrollHeight = element.prop 'scrollHeight'
				height = element.innerHeight()
				
				if scrollTop < scrollHeight - height - 25
					if $('#timsChatAutoscroll').data('status') is 1
						scrollUpNotifications = on
						do $('#timsChatAutoscroll').click
						
				if scrollTop > scrollHeight - height - 10
					if $('#timsChatAutoscroll').data('status') is 0
						scrollUpNotifications = off
						$("#timsChatMessageTabMenu > .tabMenu > ul > li.ui-state-active").removeClass 'notify'
						$(".timsChatMessageContainer.active").removeClass 'notify'
						do $('#timsChatAutoscroll').click

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
						li.addClass 'you' if user.userID is WCF.User.userID
						li.addClass 'suspended' if user.suspended
						if user.awayStatus?
							li.addClass 'away'
							li.attr 'title', user.awayStatus
						li.data 'username', user.username
						
						li.append v.userTemplate.fetch user
						
						menu = $ v.userMenuTemplate.fetch
								user: user
								room: roomList.active
						
						if menu.find('li').length
							li.append menu
							menu.addClass 'dropdownMenu'
							li.addClass 'dropdown'
							
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
			options.append = false if options.prepend? and options.prepend and not options.append?
			
			options = $.extend
				prepend: false
				append: true
				submit: false
			, options
			
			text = text + $('#timsChatInput').val() if options.prepend
			text = $('#timsChatInput').val() + text if options.append
			
			# do not insert text if it would exceed the allowed length
			maxLength = $('#timsChatInput').attr 'maxlength'
			return if maxLength? and text.length > maxLength
			
			$('#timsChatInput').val text
			$('#timsChatInput').trigger 'change'
			
			if options.submit
				do $('#timsChatForm').submit
			else
				do $('#timsChatInput').focus

Send out notifications for the given `message`. The number of unread messages will be prepended to `document.title` and if available desktop notifications will be sent.

		notify = (message) ->
			return if message.sender is WCF.User.userID
			
			if scrollUpNotifications
				$("#timsChatMessageTabMenu > .tabMenu > ul > li.ui-state-active").addClass 'notify'
				$(".timsChatMessageContainer.active").addClass 'notify'
				
			if message.isInPrivateChannel
				id = if message.sender is WCF.User.userID then message.receiver else message.sender
				
				if $('.timsChatMessageContainer.active').data('userID') isnt id
					$("#timsChatMessageTabMenuAnchor#{id}").parent().addClass 'notify'
					$("#timsChatMessageContainer#{id}").addClass 'notify'
			else if $('.timsChatMessageContainer.active').data('userID') isnt 0
				$("#timsChatMessageTabMenuAnchor0").parent().addClass 'notify'
				$("#timsChatMessageContainer0").addClass 'notify'
				
			return if isActive or $('#timsChatNotify').data('status') is 0
			
			document.title = v.titleTemplate.fetch $.extend {}, roomList.active,
				newMessageCount: ++newMessageCount
			
			title = WCF.Language.get 'chat.global.notify.title'
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
					roomList =
						active: {}
						available: {}
					
					do $('.timsChatRoom').remove
					$('#toggleRooms .badge').text data.returnValues.length
					
					for room in data.returnValues
						roomList.available[room.roomID] = room
						roomList.active = room if room.active
						
						li = $ '<li></li>'
						li.addClass('timsChatRoom').data('roomID', room.roomID)
						li.addClass 'active' if room.active
						$("""<a href="#{room.link}">#{WCF.String.escapeHTML(room.title)}</a> <span class="badge">#{WCF.String.formatNumeric room.userCount}</span>""").appendTo li
						$('#timsChatRoomList ul').append li
					
					if window.history?.replaceState?
						$('.timsChatRoom').click (event) ->
							do event.preventDefault
							
							target = $ @
							return if target.data('roomID') is roomList.active.roomID
							
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
			return if isJoining or roomID is roomList.active.roomID
			isJoining = yes
			
			do $('#timsChatInput').disable
			
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
					roomList.active = data.returnValues
					
					if openChannel is 0
						$('#timsChatTopic > .topic').text roomList.active.topic
						if roomList.active.topic.trim() is '' or hiddenTopics[roomList.active.roomID]?
							$('#timsChatTopic').addClass 'invisible'
						else
							$('#timsChatTopic').removeClass 'invisible'
					
					$('.timsChatMessage').addClass 'unloaded'
					
					document.title = v.titleTemplate.fetch roomList.active
					handleMessages roomList.active.messages
					do getMessages
					do refreshRoomList
					
					do $('#timsChatInput').enable().focus
				failure: (data) ->
					showError WCF.Language.get 'chat.error.join', data
				after: ->
					isJoining = no

Open private channel

		openPrivateChannel = (userID) ->
			userID = parseInt userID
			
			console.log "Opening private channel #{userID}"
			
			unless $.wcfIsset "timsChatMessageContainer#{userID}"
				return unless userList.allTime[userID]?
				
				div = $ '<div>'
				div.attr 'id', "timsChatMessageContainer#{userID}"
				div.data 'userID', userID
				div.addClass 'tabMenuContent'
				div.addClass 'timsChatMessageContainer'
				div.addClass 'container'
				div.addClass 'containerPadding'
				div.wrapInner "<ul></ul>"
				div.on 'scroll', (event) ->
					do event.stopPropagation
					handleScroll event
					
				$('#timsChatMessageContainer0').after div
				$('.timsChatMessageContainer').height $('.timsChatMessageContainer').height()
			
			if userID isnt 0
				if hidePrivateChannelTopic
					$('#timsChatTopic').addClass 'invisible'
				else
					$('#timsChatTopic').removeClass 'invisible'
					
				$('#timsChatTopic > .topic').html WCF.Language.get 'chat.global.privateChannelTopic', {username: userList.allTime[userID].username}
				$('#timsChatMessageTabMenu').removeClass 'singleTab'
				
				unless $.wcfIsset "timsChatMessageTabMenuAnchor#{userID}"
					li = $ '<li>'
					
					anchor = $ """<a id="timsChatMessageTabMenuAnchor#{userID}" class="timsChatMessageTabMenuAnchor" href="#{window.location.toString().replace /#.+$/, ''}#timsChatMessageContainer#{userID}" />"""
					anchor.data 'userID', userID
					
					avatar = $ userList.allTime[userID].avatar[16]
					avatar = $('<span class="userAvatar framed" />').wrapInner avatar
					avatar.append "<span>#{userList.allTime[userID].username}</span>"
					
					anchor.wrapInner avatar
					anchor.prepend '<span class="icon icon16 icon-warning-sign notifyIcon"></span>'
					anchor.append """<span class="jsChannelCloser icon icon16 icon-remove jsTooltip" title="#{WCF.Language.get('chat.global.closePrivateChannel')}" />"""
					
					li.append anchor
					
					$('#timsChatMessageTabMenu > .tabMenu > ul').append li
					$('#timsChatMessageTabMenu').wcfTabs 'refresh'
					WCF.System.FlexibleMenu.rebuild $('#timsChatMessageTabMenu > .tabMenu').attr 'id'
			else
				$('#timsChatTopic > .topic').text roomList.active.topic
				if roomList.active.topic.trim() is '' or hiddenTopics[roomList.active.roomID]?
					$('#timsChatTopic').addClass 'invisible'
				else
					$('#timsChatTopic').removeClass 'invisible'
			
			$('.timsChatMessageContainer').removeClass 'active'
			$("#timsChatMessageContainer#{userID}").addClass 'active'
			$("#timsChatMessageTabMenuAnchor#{userID}").parent().removeClass 'notify'
			$("#timsChatMessageContainer#{userID}").removeClass 'notify'
			$("#timsChatMessageContainer#{userID}").trigger 'scroll'
			
			$('#timsChatMessageTabMenu').wcfTabs 'select', $("#timsChatMessageTabMenuAnchor#{userID}").parent().index()
			do WCF.DOMNodeInsertedHandler.execute
			do $(window).resize
			
			openChannel = userID

Close private channel

		closePrivateChannel = (userID) ->
			unless userID is 0
				do $("#timsChatMessageTabMenuAnchor#{userID}").parent().remove
				do $("#timsChatMessageContainer#{userID}").remove
				$('#timsChatMessageTabMenu').wcfTabs 'refresh'
				WCF.System.FlexibleMenu.rebuild $('#timsChatMessageTabMenu > .tabMenu').wcfIdentify()
				
			if $('#timsChatMessageTabMenu > .tabMenu > ul > li').length <= 1
				$('#timsChatMessageTabMenu').addClass 'singleTab'
			
			openPrivateChannel 0

Clears a channel

		clearChannel = (userID) ->
			do $("#timsChatMessageContainer#{userID} .timsChatMessage").remove
			$("#timsChatMessageContainer#{userID}").scrollTop $("#timsChatMessageContainer#{userID}").prop 'scrollHeight'

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
					@_parentObjectID = roomList.active.roomID
					
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
						$('#timsChatUpload').addClass('uploadFailed').after """<small class="innerError">#{WCF.Language.get('wcf.attachment.upload.error.tooLarge')}</small>"""
						
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
						internalFileID = li.data 'internalFileID'
						
						if data.returnValues and data.returnValues.attachments[internalFileID]
							if data.returnValues.attachments[internalFileID].tinyURL
								li.find('.box32 > div.attachmentImageContainer > .icon-paper-clip').replaceWith $("""<img src="#{data.returnValues.attachments[internalFileID].tinyURL}'" alt="" class="attachmentTinyThumbnail" style="width: 32px; height: 32px;" />""")
								
							link = $ '<a href="" class="jsTooltip"></a>'
							link.attr {'href': data.returnValues.attachments[internalFileID].url, 'title': filename}
							
							unless parseInt(data.returnValues.attachments[internalFileID].isImage) is 0
								link.addClass('jsImageViewer')
								
								unless data.returnValues.attachments[internalFileID].tinyURL
									li.find('.box32 > div.attachmentImageContainer > .icon-paper-clip').replaceWith $("""<img src="#{data.returnValues.attachments[internalFileID].url}'" alt="" class="attachmentTinyThumbnail" style="width: 32px; height: 32px;" />""")
							
							li.find('.attachmentTinyThumbnail').wrap link
							li.find('small').append data.returnValues.attachments[internalFileID].formattedFilesize
							
							li.data 'objectID', data.returnValues.attachments[internalFileID].attachmentID
							
							deleteButton = $ """
								<li>
									<span class="jsDeleteButton" data-object-id="#{data.returnValues.attachments[internalFileID].attachmentID}" data-confirm-message="#{WCF.Language.get('wcf.attachment.delete.sure')}">
										<span class="icon icon16 icon-remove pointer jsTooltip" />
										<span>#{WCF.Language.get('wcf.global.button.delete')}</span>
									</span>
								</li>"""
							li.parent().append deleteButton
							fileUploaded = yes
						else
							$('#timsChatUpload .icon-spinner').removeClass('icon-spinner').addClass 'icon-ban-circle'
							
							if data.returnValues and data.returnValues.errors[internalFileID]
								errorMessage = data.returnValues.errors[internalFileID].errorType
							else
								errorMessage = 'uploadFailed'
							
							$('#timsChatUpload').addClass('uploadFailed').after """<small class="innerError">#{WCF.Language.get('wcf.attachment.upload.error.' + errorMessage)}</small>"""
							do $('#timsChatUploadDropdownMenu .sendAttachmentButton').remove
							do $('#timsChatUploadDropdownMenu .uploadButton').show
							fileUploaded = no
							
					do WCF.DOMNodeInsertedHandler.execute
					
					$('#timsChatUpload > span.icon').removeClass('icon-spinner').addClass 'icon-ok-sign'
					do $('#timsChatUploadDropdownMenu .uploadProgress').remove
					do $('#timsChatUploadDropdownMenu .sendAttachmentButton').show
					
				_error: (jqXHR, textStatus, errorThrown) ->
					$('#timsChatUpload > .icon-spinner').removeClass('icon-spinner').addClass 'icon-ban-circle'
					unless $('#timsChatUpload').hasClass('uploadFailed')
						$('#timsChatUpload').addClass('uploadFailed').after """<small class="innerError">#{WCF.Language.get('wcf.attachment.upload.error.uploadFailed')}</small>"""
					
					do $('#timsChatUploadDropdownMenu .uploadProgress').remove
					do $('#timsChatUploadDropdownMenu .uploadButton').show
					fileUploaded = no
					
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

Return a copy of the object containing the IDs of the marked messages

			getMarkedMessages: -> JSON.parse JSON.stringify markedMessages
			getUserList: -> JSON.parse JSON.stringify userList
			getRoomList: -> JSON.parse JSON.stringify roomList
			
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
