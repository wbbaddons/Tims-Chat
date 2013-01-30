###
# be.bastelstu.WCF.Chat
# 
# @author	Tim Düsterhus
# @copyright	2010-2013 Tim Düsterhus
# @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
# @package	be.bastelstu.chat
###

window.console ?= 
	log: () ->,
	warn: () ->,
	error: () ->

(($, window, _console) ->
	window.be ?= {}
	be.bastelstu ?= {}
	
	console =
		log: (message) ->
			_console.log "[be.bastelstu.Chat] #{message}"
		warn: (message) ->
			_console.warn "[be.bastelstu.Chat] #{message}"
		error: (message) ->
			_console.error "[be.bastelstu.Chat] #{message}"
		
	
	be.bastelstu.Chat = Class.extend
		# Tims Chat stops loading when this reaches zero
		# TODO: We need an explosion animation
		shields: 3
		
		# Are we currently loading messages?
		loading: false
		
		# Templates
		titleTemplate: null
		messageTemplate: null
		
		# Notifications
		newMessageCount: null
		isActive: true
		
		# Autocompleter
		autocompleteOffset: 0
		autocompleteValue: null
		autocompleteCaret: 0
		
		# Autoscroll
		oldScrollTop: null
		
		# Events
		events: 
			newMessage: $.Callbacks()
			userMenu: $.Callbacks()
			submit: $.Callbacks()
			
		# socket.io
		socket: null
		
		pe:
			getMessages: null
			refreshRoomList: null
			fish: null
		init: (@config, @titleTemplate, @messageTemplate) ->
			console.log 'Initializing'
			
			@events = 
				newMessage: $.Callbacks()
				userMenu: $.Callbacks()
				submit: $.Callbacks()
			
			@bindEvents()
			@events.newMessage.add $.proxy @notify, @
			
			@pe.refreshRoomList = new WCF.PeriodicalExecuter $.proxy(@refreshRoomList, @), 60e3
			@pe.getMessages = new WCF.PeriodicalExecuter $.proxy(@getMessages, @), @config.reloadTime * 1e3
			@refreshRoomList()
			@getMessages()
			@initPush()
			
			console.log 'Finished initializing - Shields at 104 percent'
		###
		# Autocompletes a username
		###
		autocomplete: (firstChars, offset = @autocompleteOffset) ->
			users = []
			
			# Search all matching users
			for user in $ '.timsChatUser'
				username = $(user).data 'username'
				if username.indexOf(firstChars) is 0
					users.push username
			
			# None found -> return firstChars
			# otherwise return the user at the current offset
			return if users.length is 0 then firstChars else users[offset % users.length] + ','
		###
		# Binds all the events needed for Tims Chat.
		###
		bindEvents: () ->
			# Mark window as focused
			$(window).focus $.proxy () ->
				document.title = @titleTemplate.fetch
					title: $('#timsChatRoomList .activeMenuItem a').text()
				@newMessageCount = 0
				@isActive = true
			, @
			
			# Mark window as blurred
			$(window).blur $.proxy () ->
				@isActive = false
			, @
			
			# Unload the chat
			$(window).on 'beforeunload', $.proxy () ->
				@unload()
				return undefined
			, @
			
			# Insert a smiley
			$('#smilies').on 'click', 'img', $.proxy (event) ->
				@insertText ' ' + $(event.target).attr('alt') + ' '
			, @
			
			# Switch sidebar tab
			$('.timsChatSidebarTabs li').click $.proxy (event) ->
				event.preventDefault()
				@toggleSidebarContents $ event.target
			, @
			
			# Submit Handler
			$('#timsChatForm').submit $.proxy (event) ->
				event.preventDefault()
				@submit $ event.target
			, @
			
			# Autocompleter
			$('#timsChatInput').keydown $.proxy (event) ->
				# tab key
				if event.keyCode is 9
					event.preventDefault()
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
					
					# Insert name and increment offset
					name = @autocomplete toComplete
					
					$('#timsChatInput').val "#{beforeComplete}#{name} #{afterComplete}"
					$('#timsChatInput').setCaret (beforeComplete + name).length + 1
					@autocompleteOffset++
				else
					@autocompleteOffset = 0
					@autocompleteValue = null
					@autocompleteCaret = null
			, @
			
			$('#timsChatInput').click $.proxy (event) ->
				@autocompleteOffset = 0
				@autocompleteValue = null
				@autocompleteCaret = null
			, @
			
			# Refreshes the roomlist
			$('#timsChatRoomList button').click $.proxy @refreshRoomList, @
			
			# Toggle Buttons
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
			
			# Clears the stream
			$('#timsChatClear').click (event) ->
				event.preventDefault()
				$('.timsChatMessage').remove()
				@oldScrollTop = null
				$('#timsChatMessageContainer').scrollTop $('#timsChatMessageContainer ul').height()
			
			$('#timsChatSmilies.click (event) ->
				if $(@).data 'status'
					$('#smilies').removeClass 'disabled'
				else
					$('#smilies').addClass 'disabled'
			
			# Enable fullscreen-mode
			$('#timsChatFullscreen').click (event) ->
				if $(@).data 'status'
					$('html').addClass 'fullscreen'
				else
					$('html').removeClass 'fullscreen'
			
			# Immediatly scroll down when activating autoscroll
			$('#timsChatAutoscroll').click (event) ->
				$(@).removeClass 'active'
				if $(@).data 'status'
					$('#timsChatMessageContainer').scrollTop $('#timsChatMessageContainer ul').height()
					@oldScrollTop = $('.timsChatMessageContainer').scrollTop()
			
			# Desktop Notifications
			unless typeof window.webkitNotifications is 'undefined'
				$('#timsChatNotify').click (event) ->
					if $(@).data('status') and window.webkitNotifications.checkPermission() isnt 0
						window.webkitNotifications.requestPermission()
			
		###
		# Changes the chat-room.
		# 
		# @param	jQuery-object	target
		###
		changeRoom: (target) ->
			window.history.replaceState {}, '', target.attr('href')
				
			$.ajax target.attr('href'), 
				dataType: 'json'
				data: 
					ajax: 1
				type: 'POST'
				success: $.proxy (data, textStatus, jqXHR) ->
					@loading = false
					target.parent().removeClass 'loading'
					
					# Mark as active
					$('.activeMenuItem .timsChatRoom').parent().removeClass 'activeMenuItem'
					target.parent().addClass 'activeMenuItem'
					
					# Set new topic
					if data.topic is ''
						return if $('#timsChatTopic').text().trim() is ''
						
						$('#timsChatTopic').wcfBlindOut 'vertical', () ->
							$(@).text ''
					else
						$('#timsChatTopic').text data.topic
						$('#timsChatTopic').wcfBlindIn() if $('#timsChatTopic').text().trim() isnt '' and $('#timsChatTopic').is ':hidden'
					
					$('.timsChatMessage').addClass 'unloaded', 800
					@handleMessages data.messages
					document.title = @titleTemplate.fetch data
				, @
				error: () ->
					# Reload the page to change the room the old fashion-way
					# inclusive the error-message :)
					window.location.reload true
				beforeSend: $.proxy(() ->
					return false if target.parent().hasClass('loading') or target.parent().hasClass 'activeMenuItem'
					
					@loading = true
					target.parent().addClass 'loading'
				, @)
		###
		# Frees the fish
		###
		freeTheFish: () ->
			return if $.wcfIsset 'fish'
			console.warn 'Freeing the fish'
			fish = $ '<div id="fish">' + WCF.String.escapeHTML('><((((\u00B0>') + '</div>'
			fish.css
				position: 'absolute'
				top: '150px'
				left: '400px'
				color: 'black'
				textShadow: '1px 1px white'
				zIndex: 9999
			
			fish.appendTo $ 'body'
			@pe.fish = new WCF.PeriodicalExecuter(() ->
				left = Math.random() * 100 - 50
				top = Math.random() * 100 - 50
				fish = $ '#fish'
				
				left *= -1 unless fish.width() < (fish.position().left + left) < ($(document).width() - fish.width())
				top *= -1 unless fish.height() < (fish.position().top + top) < ($(document).height() - fish.height())
				
				fish.text '><((((\u00B0>' if left > 0
				fish.text '<\u00B0))))><' if left < 0
				
				fish.animate
					top: '+=' + top
					left: '+=' + left
				, 1e3
			, 1.5e3)
		###
		# Loads new messages.
		###
		getMessages: () ->
			$.ajax @config.messageURL,
				dataType: 'json'
				type: 'POST'
				success: $.proxy (data, textStatus, jqXHR) ->
					WCF.DOMNodeInsertedHandler.enable()
					@handleMessages(data.messages)
					@handleUsers(data.users)
					WCF.DOMNodeInsertedHandler.disable()
				, @
				error: $.proxy (jqXHR, textStatus, errorThrown) ->
					console.error 'Battle Station hit - shields at ' + (--@shields / 3 * 104) + ' percent'
					if @shields is 0
						@pe.refreshRoomList.stop()
						@pe.getMessages.stop()
						@freeTheFish()
						console.error 'We got destroyed, but could free our friend the fish before he was killed as well. Have a nice life in freedom!'
						alert 'herp i cannot load messages'
				, @
				complete: $.proxy () ->
					@loading = false
				, @
				beforeSend: $.proxy () ->
					return false if @loading
					
					@loading = true
				, @
		###
		# Inserts the new messages.
		#
		# @param	array<object>	messages
		###
		handleMessages: (messages) ->
			# Disable scrolling automagically when user manually scrolled
			unless @oldScrollTop is null
				if $('#timsChatMessageContainer').scrollTop() < @oldScrollTop
					if $('#timsChatAutoscroll').data('status') is 1
						$('#timsChatAutoscroll').click()
						$('#timsChatAutoscroll').addClass 'active'
						$('#timsChatAutoscroll').parent().fadeOut('slow').fadeIn 'slow'
			
			# Insert the messages
			for message in messages
				continue if $.wcfIsset 'timsChatMessage' + message.messageID # Prevent problems with race condition
				@events.newMessage.fire message
				
				output = @messageTemplate.fetch message
				li = $ '<li></li>'
				li.attr 'id', 'timsChatMessage'+message.messageID
				li.addClass 'timsChatMessage timsChatMessage'+message.type
				li.addClass 'ownMessage' if message.sender is WCF.User.userID
				li.append output
				
				li.appendTo $ '#timsChatMessageContainer > ul'
				
			# Autoscroll down
			$('#timsChatMessageContainer').scrollTop $('#timsChatMessageContainer ul').height() if $('#timsChatAutoscroll').data('status') is 1
			@oldScrollTop = $('#timsChatMessageContainer').scrollTop()
		###
		# Builds the userlist.
		#
		# @param	array<object>	users
		###
		handleUsers: (users) ->
			foundUsers = { }
			for user in users
				id = 'timsChatUser-'+user.userID
				element = $ '#'+id
				
				# Move the user to the correct position
				if element[0]
					console.log 'Moving User: "' + user.username + '"'
					element = element.detach()
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
					
					$('#timsChatUserList').append element
				# Insert the user
				else
					console.log 'Inserting User: "' + user.username + '"'
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
					
					a = $ '<a>' + WCF.String.escapeHTML(user.username) + '</a>'
					a.addClass 'userLink'
					a.addClass 'dropdownToggle'
					a.data 'userID', user.userID
					a.data 'toggle', id
					
					li.append a
					
					menu = $ '<ul></ul>'
					#menu.addClass 'timsChatUserMenu'
					menu.addClass 'dropdownMenu'
					menu.append $ '<li><a>' + WCF.Language.get('chat.general.query') + '</a></li>'
					menu.append $ '<li><a>' + WCF.Language.get('chat.general.kick') + '</a></li>'
					menu.append $ '<li><a>' + WCF.Language.get('chat.general.ban') + '</a></li>'
					# TODO: SID and co
					menu.append $ '<li><a href="index.php/User/' + user.userID + '-' + encodeURI(user.username) + '/">' + WCF.Language.get('chat.general.profile') + '</a></li>'
					@events.userMenu.fire user, menu
					li.append menu
					
					li.appendTo $ '#timsChatUserList'
				
				foundUsers[id] = true
			
			# Remove users that were not found
			$('.timsChatUser').each () ->
				if typeof foundUsers[$(@).attr('id')] is 'undefined'
					console.log 'Removing User: "' + $(@).data('username') + '"'
					$(@).remove();
					
			
			$('#toggleUsers .badge').text users.length
		###
		# Initializes Server-Push
		###
		initPush: () ->
			unless typeof window.io is 'undefined'
				console.log 'Initializing nodePush'
				@socket = io.connect @config.socketIOPath
				@socket.on 'connect', $.proxy((data) ->
					console.log 'Connected to nodePush'
					@pe.getMessages.stop()
				, @)
				@socket.on 'disconnect', $.proxy (data) ->
					console.log 'Lost connection to nodePush'
					@pe.getMessages = new WCF.PeriodicalExecuter $.proxy(@getMessages, @), @config.reloadTime * 1e3
				, @
				@socket.on 'newMessage', $.proxy (data) ->
					@getMessages()
				, @
		###
		# Inserts text into our input.
		# 
		# @param	string	text
		# @param	object	options
		###
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
		###
		# Sends a notification about a message.
		#
		# @param	object	message
		###
		notify: (message) ->
			return if @isActive or $('#timsChatNotify').data('status') is 0
			@newMessageCount++
			
			document.title = '(' + @newMessageCount + ') ' + @titleTemplate.fetch
				 title: $('#timsChatRoomList .activeMenuItem a').text()
			
			# Desktop Notifications
			if typeof window.webkitNotifications isnt 'undefined'
				if window.webkitNotifications.checkPermission() is 0
					title = WCF.Language.get 'chat.general.notify.title'
					icon = "data:image/gif;base64,R0lGODlhAQABAPABAP///wAAACH5BAEKAAAALAAAAAABAAEAAAICRAEAOw%3D%3D" # empty gif
					content = message.username + message.separator + (if message.separator is ' ' then '' else ' ') + message.message
					notification = window.webkitNotifications.createNotification icon, title, content
					notification.show()
					
					# Hide notification after 10 seconds
					setTimeout () ->
						notification.cancel()
					, 10e3
		###
		# Refreshes the room-list.
		###
		refreshRoomList: () ->
			console.log 'Refreshing the roomlist'
			$('#toggleRooms .ajaxLoad').show()
			
			$.ajax $('#toggleRooms a').data('refreshUrl'),
				dataType: 'json'
				type: 'POST'
				success: $.proxy (data, textStatus, jqXHR) ->
					$('#timsChatRoomList li').remove()
					$('#toggleRooms .ajaxLoad').hide()
					$('#toggleRooms .badge').text data.length
					
					for room in data
						li = $ '<li></li>'
						li.addClass 'activeMenuItem' if room.active
						$('<a href="' + room.link + '">' + room.title + '</a>').addClass('timsChatRoom').appendTo li
						$('#timsChatRoomList ul').append li
						
					$('.timsChatRoom').click $.proxy (event) ->
						return if typeof window.history.replaceState is 'undefined'
						event.preventDefault()
						@changeRoom $ event.target
					, @
					
					console.log "Found #{data.length} rooms"
				, @
		###
		# Handles submitting of messages.
		# 
		# @param	jQuery-object	target
		###
		submit: (target) ->
			# Break if input contains only whitespace
			return false if $('#timsChatInput').val().trim().length is 0
			
			# Finally free the fish
			@freeTheFish() if $('#timsChatInput').val().trim().toLowerCase() is '/free the fish'
			
			text = $('#timsChatInput').val()
			
			# call submit event
			# TODO: Fix this
			# text = @events.submit.fire text
			
			$('#timsChatInput').val('').focus().keyup()
			$.ajax $('#timsChatForm').attr('action'), 
				data:
					text: text
					smilies: $('#timsChatSmilies').data 'status'
				type: 'POST',
				beforeSend: (jqXHR) ->
				success: $.proxy (data, textStatus, jqXHR) ->
					@getMessages()
				, @
				complete: () ->
		###
		# Toggles between user- and room-list.
		# 
		# @param	jQuery-object	target
		###
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
		###
		# Unloads the chat.
		###
		unload: () ->
			$.ajax @config.unloadURL,
				type: 'POST'
				async: false
)(jQuery, @, console)
