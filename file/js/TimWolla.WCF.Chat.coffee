###
# TimWolla.WCF.Chat
# 
# @author	Tim Düsterhus
# @copyright	2010-2011 Tim Düsterhus
# @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
# @package	timwolla.wcf.chat
###

TimWolla ?= {}
TimWolla.WCF ?= {}

(($, window) ->
	TimWolla.WCF.Chat =
		# Templates
		titleTemplate: null
		messageTemplate: null
		
		# Notifications
		newMessageCount: null
		isActive: true
		
		# Autocompleter
		autocompleteOffset: 0
		autocompleteValue: null
		
		# Autoscroll
		oldScrollTop: null
		
		# Events
		events: 
			newMessage: $.Callbacks()
			userMenu: $.Callbacks()
		init: () ->
			console.log('[TimWolla.WCF.Chat] Initializing');
			@bindEvents()
			@events.newMessage.add $.proxy @notify, @
			
			new WCF.PeriodicalExecuter $.proxy(@refreshRoomList, @), 60e3
			new WCF.PeriodicalExecuter $.proxy(@getMessages, @), @config.reloadTime * 1e3
			@refreshRoomList()
			@getMessages()
			
			console.log '[TimWolla.WCF.Chat] Finished initializing'
		###
		# Autocompletes a username
		###
		autocomplete: (firstChars, offset = @autocompleteOffset) ->
			users = []
			
			# Search all matching users
			for user in $ '.chatUser'
				username = $(user).data('username');
				if username.indexOf(firstChars) is 0
					users.push username
			
			# None found -> return firstChars
			# otherwise return the user at the current offset
			return if users.length is 0 then firstChars else users[offset % users.length]
		###
		# Binds all the events needed for Tims Chat.
		###
		bindEvents: () ->
			$(window).focus $.proxy () ->
				document.title = @titleTemplate.fetch({ title: $('#chatRoomList .activeMenuItem a').text() })
				@newMessageCount = 0
				@isActive = true
			, @
			
			$(window).blur $.proxy () ->
				@isActive = false
			, @
			
			# Insert a smiley
			$('.smiley').click $.proxy (event) ->
				@insertText ' ' + $(event.target).attr('alt') + ' '
			, @
			
			# Switch sidebar tab
			$('.chatSidebarTabs li').click $.proxy (event) ->
				event.preventDefault()
				@toggleSidebarContents $ event.target
			, @
			
			# Submit Handler
			$('#chatForm').submit $.proxy (event) ->
				event.preventDefault()
				@submit $ event.target
			, @
			
			# Autocompleter
			$('#chatInput').keydown $.proxy (event) ->
				# tab key
				if event.keyCode is 9
					event.preventDefault()
					if @autocompleteValue is null
						@autocompleteValue = $('#chatInput').val()
					
					firstChars = @autocompleteValue.substring(@autocompleteValue.lastIndexOf(' ')+1)
					
					console.log '[TimWolla.WCF.Chat] Autocompleting "' + firstChars + '"'
					return if firstChars.length is 0
					
					# Insert name and increment offset
					$('#chatInput').val(@autocompleteValue.substring(0, @autocompleteValue.lastIndexOf(' ')+1) + @autocomplete(firstChars) + ', ')
					@autocompleteOffset++
				else
					@autocompleteOffset = 0
					@autocompleteValue = null
			, @
			
			# Clears the stream
			$('#chatClear').click (event) ->
				event.preventDefault()
				$('.chatMessage').remove()
				$('#chatInput').focus()
			
			# Toggle Buttons
			$('.chatToggle').click (event) ->
				element = $ @
				icon = element.find 'img'
				if element.data('status') is 1
					element.data 'status', 0
					icon.attr 'src', icon.attr('src').replace /enabled(\d?).([a-z]{3})$/, 'disabled$1.$2'
					element.attr 'title', element.data 'enableMessage'
				else
					element.data 'status', 1
					icon.attr 'src', icon.attr('src').replace /disabled(\d?).([a-z]{3})$/, 'enabled$1.$2'
					element.attr 'title', element.data 'disableMessage'
					
			# Immediatly scroll down when activating autoscroll
			$('#chatAutoscroll').click (event) ->
				$(this).removeClass('hot')
				if $(this).data 'status'
					$('.chatMessageContainer').scrollTop $('.chatMessageContainer ul').height()
				
			# Desktop Notifications
			unless typeof window.webkitNotifications is 'undefined'
				$('#chatNotify').click (event) ->
					window.webkitNotifications.requestPermission() if $(this).data 'status'
					
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
				success: $.proxy((data, textStatus, jqXHR) ->
					@loading = false
					target.parent().removeClass 'ajaxLoad'
					
					# Mark as active
					$('.activeMenuItem .chatRoom').parent().removeClass 'activeMenuItem'
					target.parent().addClass 'activeMenuItem'
					
					# Set new topic
					if data.topic is ''
						return if $('#topic').text().trim() is ''
						
						$('#topic').wcfBlindOut 'vertical', () ->
							$(@).text ''
					else
						$('#topic').text data.topic
						$('#topic').wcfBlindIn() if $('#topic').text().trim() isnt '' and $('#topic').is(':hidden')
					
					$('title').text @titleTemplate.fetch(data)
					@getMessages()
				, @)
				error: () ->
					# Reload the page to change the room the old fashion-way
					# inclusive the error-message :)
					window.location.reload true
				beforeSend: $.proxy(() ->
					return false if @loading or target.parent().hasClass 'activeMenuItem'
					
					@loading = true
					target.parent().addClass 'ajaxLoad'
				, @)
		###
		# Frees the fish
		###
		freeTheFish: () ->
			return if $.wcfIsset('fish')
			console.warn '[TimWolla.WCF.Chat] Freeing the fish'
			fish = $ '<div id="fish">' + WCF.String.escapeHTML('><((((\u00B0>') + '</div>'
			fish.css
				position: 'absolute'
				top: '150px'
				left: '400px'
				color: 'black'
				textShadow: '1px 1px white'
				zIndex: 9999
			
			fish.appendTo $ 'body'
			new WCF.PeriodicalExecuter(() ->
				left = Math.random() * 100 - 50
				top = Math.random() * 100 - 50
				fish = $('#fish')
				
				left *= -1 unless fish.width() < (fish.position().left + left) < ($(document).width() - fish.width())
				top *= -1 unless fish.height() < (fish.position().top + top) < ($(document).height() - fish.height())
				
				fish.text('><((((\u00B0>') if left > 0
				fish.text('<\u00B0))))><') if left < 0
				
				fish.animate
					top: '+=' + top
					left: '+=' + left
				, 1e3
			, 1.5e3);
		###
		# Loads new messages.
		###
		getMessages: () ->
			$.ajax 'index.php/Chat/Message/',
				dataType: 'json'
				type: 'POST'
				success: $.proxy((data, textStatus, jqXHR) ->
					@handleMessages(data.messages)
					@handleUsers(data.users)
				, @)
		###
		# Inserts the new messages.
		#
		# @param	array<object>	messages
		###
		handleMessages: (messages) ->
			# Disable scrolling automagically when user manually scrolled
			unless @oldScrollTop is null
				if $('.chatMessageContainer').scrollTop() isnt @oldScrollTop
					if $('#chatAutoscroll').data('status') is 1
						$('#chatAutoscroll').click()
						$('#chatAutoscroll').addClass('hot').fadeOut('slow').fadeIn('slow')
			
			# Insert the messages
			for message in messages
				@events.newMessage.fire message
				
				output = @messageTemplate.fetch message
				li = $ '<li></li>'
				li.addClass 'chatMessage chatMessage'+message.type
				li.addClass 'ownMessage' if message.sender is WCF.User.userID
				li.append output
				
				li.appendTo $ '.chatMessageContainer ul'
				
			# Autoscroll down
			if $('#chatAutoscroll').data('status') is 1
				$('.chatMessageContainer').scrollTop $('.chatMessageContainer ul').height()
				@oldScrollTop = $('.chatMessageContainer').scrollTop()
		###
		# Builds the userlist.
		#
		# @param	array<object>	users
		###
		handleUsers: (users) ->
			foundUsers = {}
			for user in users
				id = 'chatUser-'+user.userID
				element = $('#'+id)
				
				# Move the user to the correct position
				if element[0]
					console.log '[TimWolla.WCF.Chat] Shifting user ' + user.userID
					element = element.detach()
					$('#chatUserList').append element
				# Insert the user
				else
					console.log '[TimWolla.WCF.Chat] Inserting user ' + user.userID
					li = $ '<li></li>'
					li.attr 'id', id
					li.addClass 'chatUser'
					li.data 'username', user.username
					a = $ '<a href="javascript:;">'+user.username+'</a>'
					a.click $.proxy (event) ->
						event.preventDefault()
						@toggleUserMenu $ event.target
					, @
					li.append a
					menu = $ '<ul></ul>'
					menu.addClass 'chatUserMenu'
					menu.append $ '<li><a href="javascript:;">' + WCF.Language.get('wcf.chat.query') + '</a></li>'
					menu.append $ '<li><a href="javascript:;">' + WCF.Language.get('wcf.chat.kick') + '</a></li>'
					menu.append $ '<li><a href="javascript:;">' + WCF.Language.get('wcf.chat.ban') + '</a></li>'
					menu.append $ '<li><a href="index.php/User/' + user.userID + '">' + WCF.Language.get('wcf.chat.profile') + '</a></li>'
					@events.userMenu.fire user, menu
					li.append menu
					li.appendTo $ '#chatUserList'
				
				foundUsers[id] = true
			
			$('.chatUser').each () ->
				if typeof foundUsers[$(@).attr('id')] is 'undefined'
					$(@).remove()
			
			$('#toggleUsers .badge').text(users.length);
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
			
			text = $('#chatInput').val() + text if options.append
			$('#chatInput').val(text)
			$('#chatInput').keyup()
			
			if (options.submit)
				$('#chatForm').submit()
			else
				$('#chatInput').focus()
		###
		# Sends a notification about a message.
		#
		# @param	object	message
		###
		notify: (message) ->
			return if @isActive or $('#chatNotify').data('status') is 0
			@newMessageCount++
			
			document.title = '(' + @newMessageCount + ') ' + @titleTemplate.fetch({ title: $('#chatRoomList .activeMenuItem a').text() })
			
			# Desktop Notifications
			if typeof window.webkitNotifications isnt 'undefined'
				if window.webkitNotifications.checkPermission() is 0
					notification = window.webkitNotifications.createNotification WCF.Icon.get('timwolla.wcf.chat.chat'), WCF.Language.get('wcf.chat.newMessages'), message.username + ' ' + message.message
					notification.show()
					setTimeout(() ->
						notification.cancel()
					, 5000)
		###
		# Refreshes the room-list.
		###
		refreshRoomList: () ->
			console.log '[TimWolla.WCF.Chat] Refreshing the roomlist'
			$('#toggleRooms a').addClass 'ajaxLoad'
			
			$.ajax $('#toggleRooms a').data('refreshUrl'),
				dataType: 'json'
				type: 'POST'
				success: $.proxy((data, textStatus, jqXHR) ->
					$('#chatRoomList li').remove()
					$('#toggleRooms a').removeClass 'ajaxLoad'
					$('#toggleRooms .badge').text(data.length);
					
					for room in data
						li = $ '<li></li>'
						li.addClass 'activeMenuItem' if room.active
						$('<a href="' + room.link + '">' + room.title + '</a>').addClass('chatRoom').appendTo li
						$('#chatRoomList ul').append li
						
					$('.chatRoom').click $.proxy (event) ->
						return if typeof window.history.replaceState is 'undefined'
						event.preventDefault()
						@changeRoom $ event.target
					, @
					
					console.log '[TimWolla.WCF.Chat] Found ' + data.length + ' rooms'
				, @)
		###
		# Handles submitting of messages.
		# 
		# @param	jQuery-object	target
		###
		submit: (target) ->
			# Break if input contains only whitespace
			return false if $('#chatInput').val().trim().length is 0
			
			# Finally free the fish
			@freeTheFish() if $('#chatInput').val().trim().toLowerCase() is '/free the fish'
			
			$.ajax $('#chatForm').attr('action'), 
				data:
					text: $('#chatInput').val(),
					smilies: $('#chatSmilies').data('status')
				type: 'POST',
				beforeSend: (jqXHR) ->
					$('#chatInput').addClass 'ajaxLoad'
				success: $.proxy((data, textStatus, jqXHR) ->
					@getMessages()
					$('#chatInput').val('').focus()
					$('#chatInput').keyup()
				, @)
				complete: () ->
					$('#chatInput').removeClass 'ajaxLoad'
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
				
				$('#chatRoomList').hide()
				$('#chatUserList').show()
			else if target.parents('li').attr('id') is 'toggleRooms'
				$('#toggleRooms').addClass 'active'
				$('#toggleUsers').removeClass 'active'
				
				$('#chatUserList').hide()
				$('#chatRoomList').show()
		###
		# Toggles the user-menu.
		#
		# @param	jQuery-object	target
		###
		toggleUserMenu: (target) ->
			li = target.parent()
			
			if li.hasClass 'activeMenuItem'
				li.find('.chatUserMenu').wcfBlindOut 'vertical', () ->
					li.removeClass 'activeMenuItem'
			else
				li.addClass 'activeMenuItem'
				li.find('.chatUserMenu').wcfBlindIn 'vertical'
)(jQuery, @)
