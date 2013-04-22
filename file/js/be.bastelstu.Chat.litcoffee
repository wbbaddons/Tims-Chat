Main JavaScript file for Tims Chat
==================================
Copyright Information
---------------------

	"@author	Tim Düsterhus"
	"@copyright	2010-2013 Tim Düsterhus"
	"@license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>"
	"@package	be.bastelstu.chat"

Setup
-----
Ensure sane values for `$` and `window`

	(($, window) ->
		# Enable strict mode
		"use strict";
		
		# Ensure our namespace is present
		window.be ?= {}
		be.bastelstu ?= {}

Overwrite `console` to add the origin in front of the message

		console =
			log: (message) ->
				window.console.log "[be.bastelstu.Chat] #{message}"
			warn: (message) ->
				window.console.warn "[be.bastelstu.Chat] #{message}"
			error: (message) ->
				window.console.error "[be.bastelstu.Chat] #{message}"
be.bastelstu.Chat
=================

		be.bastelstu.Chat = Class.extend

Attributes
----------

When `shields` reaches zero `@pe.getMessages` is stopped, to prevent annoying the server with requests that don't go through. Decreased every time `@getMessages()` fails.		

			shields: 3
			
Prevents loading messages in parallel.

			loading: false
			
Instances of `WCF.Template`

			titleTemplate: null
			messageTemplate: null
			userTemplate: null
			
Attributes needed for notificationss

			newMessageCount: null
			isActive: true
			
Attributes needed for autocompleter

			autocompleteOffset: 0
			autocompleteValue: null
			autocompleteCaret: 0
			
Attributes needed for automated scrolling
			
			oldScrollTop: null
			
Events one can listen to. Allows 3rd party developers to change data shown in the chat by appending a callback.

			events: 
				newMessage: $.Callbacks()
				userMenu: $.Callbacks()
				submit: $.Callbacks()
				
Instance of socket.io for real time chatting.

			socket: null
			
Every `WCF.PeriodicalExecuter` used by the chat to allow access for 3rd party developers.

			pe:
				getMessages: null
				refreshRoomList: null
				fish: null
				
Methods
-------

**init(@config, @titleTemplate, @messageTemplate, @userTemplate)**  
Constructor, binds needed events and initializes `@events` and `PeriodicalExecuter`s.

			init: (@config, @titleTemplate, @messageTemplate, @userTemplate) ->
				console.log 'Initializing'

Bind events and initialize our own event system.

				@events = 
					newMessage: $.Callbacks()
					userMenu: $.Callbacks()
					submit: $.Callbacks()
				
				@bindEvents()
				@events.newMessage.add $.proxy @notify, @
				
Initialize `PeriodicalExecuter` and run them once.

				@pe.refreshRoomList = new WCF.PeriodicalExecuter $.proxy(@refreshRoomList, @), 60e3
				@pe.getMessages = new WCF.PeriodicalExecuter $.proxy(@getMessages, @), @config.reloadTime * 1e3
				@refreshRoomList()
				@getMessages()
				
Initialize `nodePush`

				@initPush()

Finished!

				console.log 'Finished initializing - Shields at 104 percent'

**autocomplete(firstChars, offset = @autocompleteOffset)**  
Autocompletes a username based on the `firstChars` given and the given `offset`. `offset` allows to skip users.

			autocomplete: (firstChars, offset = @autocompleteOffset) ->
				
Create an array of active chatters with usernames beginning with `firstChars`

				users = [ ]
				
				for user in $ '.timsChatUser'
					username = $(user).data 'username'
					if username.indexOf(firstChars) is 0
						users.push username
				
If no matching user is found return `firstChars`, return the user at the given `offset` with a trailing comma otherwise.

				return if users.length is 0 then firstChars else users[offset % users.length] + ','

**bindEvents()**  
Binds needed DOM events.

			bindEvents: ->

Mark chat as `@isActive` and reset `document.title` to default title, thus removing the number of new messages.

				$(window).focus =>
					document.title = @titleTemplate.fetch
						title: $('#timsChatRoomList .activeMenuItem a').text()
					@newMessageCount = 0
					@isActive = true
				
Mark chat as inactive, thus enabling notifications.

				$(window).blur =>
					@isActive = false
				
Calls the unload handler (`@unload`) before unloading the chat.

				$(window).on 'beforeunload', =>
					@unload()
					undefined
				
Inserts a smiley into the input.

				$('#smilies').on 'click', 'img', (event) =>
					@insertText ' ' + $(event.target).attr('alt') + ' '
				

Switches the active sidebar tab.

				$('.timsChatSidebarTabs li').click (event) =>
					event.preventDefault()
					@toggleSidebarContents $ event.target
				
				
Calls the submit handler (`@submit`) when the `#timsChatForm` is `submit`ted.

				$('#timsChatForm').submit (event) =>
					event.preventDefault()
					@submit $ event.target
				
				
Autocompletes a username when TAB is pressed.

				$('#timsChatInput').keydown (event) =>
					if event.keyCode is 9
						event.preventDefault()
	
Calculate `firstChars` to autocomplete, based on the caret position.

						@autocompleteValue = $('#timsChatInput').val() if @autocompleteValue is null
						@autocompleteCaret = $('#timsChatInput').getCaret() if @autocompleteCaret is null
						
						beforeCaret = @autocompleteValue.substring 0, @autocompleteCaret
						lastSpace = beforeCaret.lastIndexOf ' '
						beforeComplete = @autocompleteValue.substring 0, lastSpace + 1
						toComplete = @autocompleteValue.substring lastSpace + 1
						nextSpace = toComplete.indexOf ' '
						if nextSpace is -1
							afterComplete = '';
						else
							afterComplete = toComplete.substring nextSpace + 1
							toComplete = toComplete.substring 0, nextSpace
						
						return if toComplete.length is 0
						console.log "Autocompleting '#{toComplete}'"
						
Insert completed value into `#timsChatInput`
						
						name = @autocomplete toComplete
						
						$('#timsChatInput').val "#{beforeComplete}#{name} #{afterComplete}"
						$('#timsChatInput').setCaret (beforeComplete + name).length + 1
						@autocompleteOffset++
						
Resets autocompleter to default status, when a key is pressed that is not TAB.

					else
						@autocompleteOffset = 0
						@autocompleteValue = null
						@autocompleteCaret = null
				
Resets autocompleter to default status, when input is `click`ed, as the position of the caret may have changed.

				$('#timsChatInput').click =>
					@autocompleteOffset = 0
					@autocompleteValue = null
					@autocompleteCaret = null
				
Refreshes the room list when the associated button is `click`ed.

				$('#timsChatRoomList button').click $.proxy @refreshRoomList, @
				
Clears the chat, by removing every single message.

				$('#timsChatClear').click (event) ->
					event.preventDefault()
					$('.timsChatMessage').remove()
					@oldScrollTop = null
					$('#timsChatMessageContainer').scrollTop $('#timsChatMessageContainer ul').height()
				
Handling toggling when a toggable button is `click`ed.

				$('.timsChatToggle').click (event) ->
					element = $ @
					icon = element.find 'span.icon'
					if element.data('status') is 1
						element.data 'status', 0
						icon.removeClass('icon-circle-blank').addClass('icon-off')
						element.attr 'title', element.data 'enableMessage'
					else
						element.data 'status', 1
						icon.removeClass('icon-off').addClass('icon-circle-blank')
						element.attr 'title', element.data 'disableMessage'
						
					$('#timsChatInput').focus()

Mark smilies as disabled.

				$('#timsChatSmilies').click (event) ->
					if $(@).data 'status'
						$('#smilies').removeClass 'disabled'
					else
						$('#smilies').addClass 'disabled'

Toggle fullscreen mode.

				$('#timsChatFullscreen').click (event) ->
					if $(@).data 'status'
						$('html').addClass 'fullscreen'
					else
						$('html').removeClass 'fullscreen'
				
Scroll down when autoscroll is being activated.

				$('#timsChatAutoscroll').click (event) ->
					$(@).removeClass 'active'
					if $(@).data 'status'
						$('#timsChatMessageContainer').scrollTop $('#timsChatMessageContainer ul').height()
						@oldScrollTop = $('.timsChatMessageContainer').scrollTop()

Ask for permissions to use Desktop notifications when notifications are activated.

				if window.Notification?
					$('#timsChatNotify').click (event) ->
						return unless $(@).data 'status'
						if window.Notification.permission isnt 'granted'
							window.Notification.requestPermission (permission) ->
								window.Notification.permission ?= permission

**changeRoom(target)**  
Change the active chatroom. `target` is the link clicked.

			changeRoom: (target) ->
			
Update URL to target URL by using `window.history.replaceState()`.

				window.history.replaceState {}, '', target.attr('href')
					
				$.ajax target.attr('href'), 
					dataType: 'json'
					data: 
						ajax: 1
					type: 'POST'
					success: (data, textStatus, jqXHR) =>
						@loading = false
						target.parent().removeClass 'loading'
						
						# Mark as active
						$('.activeMenuItem .timsChatRoom').parent().removeClass 'activeMenuItem'
						target.parent().addClass 'activeMenuItem'
						
Update topic, hiding and showing the topic container when necessary.

						$('#timsChatTopic').text data.topic
						if data.topic is ''
							$('#timsChatTopic').addClass 'empty'
						else
							$('#timsChatTopic').removeClass 'empty'

Mark old messages as `unloaded`.

						$('.timsChatMessage').addClass 'unloaded'
						
Show the messages written before entering the room to get a quick glance at the current topic.

						@handleMessages data.messages

Update `document.title` to reflect the cnew room.

						document.title = @titleTemplate.fetch data
						
Fix smiley category URLs, as the URL changed.

						$('#smilies .menu li a').each (key, value) ->
							anchor = $(value)
							anchor.attr 'href', anchor.attr('href').replace /.*#/, "#{target.attr('href')}#"

Reload the whole page when an error occurs. The users thus sees the error message (usually `PermissionDeniedException`)

					error: ->
						window.location.reload true

Show loading icon and prevent switching the room in parallel.

					beforeSend: =>
						return false if target.parent().hasClass('loading') or target.parent().hasClass 'activeMenuItem'
						
						@loading = true
						target.parent().addClass 'loading'

**freeTheFish()**  
Free the fish!

			freeTheFish: ->
				return if $.wcfIsset 'fish'
				console.warn 'Freeing the fish'
				fish = $ """<div id="fish">#{WCF.String.escapeHTML('><((((\u00B0>')}</div>"""
				fish.css
					position: 'absolute'
					top: '150px'
					left: '400px'
					color: 'black'
					textShadow: '1px 1px white'
					zIndex: 9999
				
				fish.appendTo $ 'body'
				@pe.fish = new WCF.PeriodicalExecuter () ->
					left = Math.random() * 100 - 50
					top = Math.random() * 100 - 50
					fish = $ '#fish'
					
					left *= -1 unless fish.width() < (fish.position().left + left) < ($(document).width() - fish.width())
					top *= -1 unless fish.height() < (fish.position().top + top) < ($(document).height() - fish.height())
					
					fish.text '><((((\u00B0>' if left > 0
					fish.text '<\u00B0))))><' if left < 0
					
					fish.animate
						top: "+=#{top}"
						left: "+=#{left}"
					, 1e3
				, 1.5e3

**getMessages()**  
Loads new messages.

			getMessages: ->
				$.ajax @config.messageURL,
					dataType: 'json'
					type: 'POST'

Handle reply.

					success: (data, textStatus, jqXHR) =>
						WCF.DOMNodeInsertedHandler.enable()
						@handleMessages(data.messages)
						@handleUsers(data.users)
						WCF.DOMNodeInsertedHandler.disable()

Decrease `@shields` on error and disable PeriodicalExecuters once `@shields` reaches zero.

					error: =>
						console.error 'Battle Station hit - shields at ' + (--@shields / 3 * 104) + ' percent'
						if @shields is 0
							@pe.refreshRoomList.stop()
							@pe.getMessages.stop()
							@freeTheFish()
							console.error 'We got destroyed, but could free our friend the fish before he was killed as well. Have a nice life in freedom!'
							alert 'herp i cannot load messages'
					complete: =>
						@loading = false

Prevent loading messages in parallel, as this leads to several problems.

					beforeSend: =>
						return false if @loading
						
						@loading = true

**handleMessages(messages)**  
Inserts the `messages` given into the stream.

			handleMessages: (messages) ->

Disable autoscroll when the user scrolled up to read old messages

				unless @oldScrollTop is null
					if $('#timsChatMessageContainer').scrollTop() < @oldScrollTop
						if $('#timsChatAutoscroll').data('status') is 1
							$('#timsChatAutoscroll').click()
							$('#timsChatAutoscroll').addClass 'active'
							$('#timsChatAutoscroll').parent().fadeOut('slow').fadeIn 'slow'

Insert the new messages.

				for message in messages

Prevent problems with race condition

					continue if $.wcfIsset "timsChatMessage#{message.messageID}"

Call the `@events.newMessage` event.

					@events.newMessage.fire message

Build HTML of the message and append it to our current message list

					output = @messageTemplate.fetch message
					li = $ '<li></li>'
					li.attr 'id', "timsChatMessage#{message.messageID}"
					li.addClass 'timsChatMessage timsChatMessage'+message.type
					li.addClass 'ownMessage' if message.sender is WCF.User.userID
					li.append output
					
					li.appendTo $ '#timsChatMessageContainer > ul'
					

Scroll down when autoscrolling is enabled.

				$('#timsChatMessageContainer').scrollTop $('#timsChatMessageContainer ul').height() if $('#timsChatAutoscroll').data('status') is 1
				@oldScrollTop = $('#timsChatMessageContainer').scrollTop()

**handleUsers(users)**  
Rebuild the userlist containing `users` afterwards.

			handleUsers: (users) ->

Keep track of the users that did not leave.

				foundUsers = { }

Loop all users.

				for user in users
					id = "timsChatUser-#{user.userID}"
					element = $ "##{id}"

Move the user, to prevent rebuilding the entire user list.

					if element[0]
						console.log "Moving User: '#{user.username}'"
						element = element.detach()
						
						if user.awayStatus?
							element.addClass 'away'
							element.attr 'title', user.awayStatus
						else
							element.removeClass 'timsChatAway'
							element.removeAttr 'title'
							element.data 'tooltip', ''
						
						if user.suspended
							element.addClass 'suspended'
						else
							element.removeClass 'suspended'
						
						$('#timsChatUserList').append element

Build HTML of new user and append it.

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
							li.addClass 'timsChatAway'
							li.attr 'title', user.awayStatus
						li.data 'username', user.username
						
						li.append @userTemplate.fetch
							user: user
						
						menu = $ '<ul></ul>'
						menu.addClass 'dropdownMenu'
						menu.append $ "<li><a>#{WCF.Language.get('chat.general.query')}</a></li>"
						menu.append $ "<li><a>#{WCF.Language.get('chat.general.kick')}</a></li>"
						menu.append $ "<li><a>#{WCF.Language.get('chat.general.ban')}</a></li>"
						# TODO: SID and co
						menu.append $ """<li><a href="index.php/User/#{user.userID}-#{encodeURI(user.username)}/">#{WCF.Language.get('chat.general.profile')}</a></li>"""
						@events.userMenu.fire user, menu
						li.append menu
						
						li.appendTo $ '#timsChatUserList'
					
					foundUsers[id] = true

Remove all users that left the chat.

				$('.timsChatUser').each () ->
					unless foundUsers[$(@).attr('id')]?
						console.log "Removing User: '#{$(@).data('username')}'"
						$(@).remove();
						
				
				$('#toggleUsers .badge').text users.length

**initPush()**  
Initialize socket.io to enable nodePush.

			initPush: ->
				if window.io?
					console.log 'Initializing nodePush'
					@socket = io.connect @config.socketIOPath
					
					@socket.on 'connect', =>
						console.log 'Connected to nodePush'

Disable `@pe.getMessages` once we are connected.

						@pe.getMessages.stop()
						
					@socket.on 'disconnect', =>
						console.log 'Lost connection to nodePush'

Reenable `@pe.getMessages` once we are disconnected.

						@pe.getMessages = new WCF.PeriodicalExecuter $.proxy(@getMessages, @), @config.reloadTime * 1e3
						
					@socket.on 'newMessage', =>
						@getMessages()

**insertText(text, options)**  
Inserts the given `text` into the input. If `options.append` is truthy the given `text` will be appended and replaces the existing text otherwise. If `options.submit` is truthy the message will be submitted afterwards.

			insertText: (text, options) ->
				options = $.extend
					append: true
					submit: false
				, options or {}
				
				text = $('#timsChatInput').val() + text if options.append
				$('#timsChatInput').val text
				$('#timsChatInput').keyup()
				
				if (options.submit)
					$('#timsChatForm').submit()
				else
					$('#timsChatInput').focus()

**notify(message)**  
Sends out notifications for the given `message`. The number of unread messages will be prepended to `document.title` and if available desktop notifications will be sent.

			notify: (message) ->
				return if @isActive or $('#timsChatNotify').data('status') is 0
				@newMessageCount++
				
				document.title = '(' + @newMessageCount + ') ' + @titleTemplate.fetch
					 title: $('#timsChatRoomList .activeMenuItem a').text()
				
				# Desktop Notifications
				title = WCF.Language.get 'chat.general.notify.title'
				content = "#{message.username}#{message.separator} #{message.message}"
				
				if window.Notification?
					if window.Notification.permission is 'granted'
						do ->
							notification = new window.Notification title,
								body: content
								onclick: ->
									notification.close()
							setTimeout ->
								notification.close()
							, 5e3

**refreshRoomList()**  
Updates the room list. 

			refreshRoomList: ->
				console.log 'Refreshing the roomlist'
				$('#toggleRooms .ajaxLoad').show()
				
				proxy = new WCF.Action.Proxy
					autoSend: true
					data:
						actionName: 'getRoomList'
						className: 'chat\\data\\room\\RoomAction'
					showLoadingOverlay: false
					success: (data) =>
						$('#timsChatRoomList li').remove()
						$('#toggleRooms .ajaxLoad').hide()
						$('#toggleRooms .badge').text data.returnValues.length
						
						for room in data.returnValues
							li = $ '<li></li>'
							li.addClass 'activeMenuItem' if room.active
							$("""<a href="#{room.link}">#{room.title}</a>""").addClass('timsChatRoom').appendTo li
							$('#timsChatRoomList ul').append li

Bind click event for inline room change if we have the history API available.

						if window.history?.replaceState?
							$('.timsChatRoom').click (event) =>
								event.preventDefault()
								@changeRoom $ event.target
						
						console.log "Found #{data.length} rooms"

**submit(target)**  
Submits the message.

			submit: (target) ->
				# Break if input contains only whitespace
				return false if $('#timsChatInput').val().trim().length is 0
				
				# Free the fish!
				@freeTheFish() if $('#timsChatInput').val().trim().toLowerCase() is '/free the fish'
				
				text = $('#timsChatInput').val()
				
				# call submit event
				# TODO: Fix this
				# text = @events.submit.fire text
				
				$('#timsChatInput').val('').focus().keyup()
				
				proxy = new WCF.Action.Proxy
					autoSend: true
					data:
						actionName: 'send'
						className: 'chat\\data\\message\\MessageAction'
						parameters:
							text: text
							enableSmilies: $('#timsChatSmilies').data 'status'
					showLoadingOverlay: false
					success: =>
						$('#timsChatInputContainer').removeClass('formError').find('.innerError').hide()
						@getMessages()
					failure: (data) =>
						return true if not (data?.returnValues?.errorType?) and not (data?.message?)
						
						$('#timsChatInputContainer').addClass('formError').find('.innerError').show().html (data?.returnValues?.errorType) ? data.message
						false

**toggleSidebarContents(target)**  
Switches the active sidebar tab to the one belonging to `target`.

			toggleSidebarContents: (target) ->
				return if target.parents('li').hasClass 'active'
				
				if target.parents('li').attr('id') is 'toggleUsers'
					$('#toggleUsers').addClass 'active'
					$('#toggleRooms').removeClass 'active'
					
					$('#timsChatRoomList').hide()
					$('#timsChatUserList').show()
				else if target.parents('li').attr('id') is 'toggleRooms'
					$('#toggleRooms').addClass 'active'
					$('#toggleUsers').removeClass 'active'
					
					$('#timsChatUserList').hide()
					$('#timsChatRoomList').show()

**unload()**  
Sends leave notification to the server.

			unload: ->
				$.ajax @config.unloadURL,
					type: 'POST'
					async: false
	)(jQuery, @)
