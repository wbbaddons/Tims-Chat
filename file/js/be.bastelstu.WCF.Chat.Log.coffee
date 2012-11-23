###
# be.bastelstu.WCF.Chat.Log
# 
# @author	Tim Düsterhus
# @copyright	2010-2012 Tim Düsterhus
# @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
# @package	be.bastelstu.wcf.chat
###

(($, window, _console) ->
	be.bastelstu.WCF.Chat.Log = $.extend true, { }, be.bastelstu.WCF.Chat, 
		init: () ->
			console.log 'Initializing'
			@bindEvents()
			console.log 'Finished initializing - Shields at 104 percent'
		###
		# Binds all the events needed for Tims Chat.
		###
		bindEvents: () ->
			# Switch sidebar tab
			$('.timsChatSidebarTabs li').click $.proxy (event) ->
				event.preventDefault()
				@toggleSidebarContents $ event.target
			, @
			
			# Refreshes the roomlist
			$('#timsChatRoomList button').click $.proxy(@refreshRoomList, @)
			
			# Toggle Buttons
			$('.timsChatToggle').click (event) ->
				element = $ @
				icon = element.find 'img'
				if element.data('status') is 1
					element.data 'status', 0
					icon.attr 'src', icon.attr('src').replace /enabled(Inverse)?.([a-z]{3})$/, 'disabled$1.$2'
					element.attr 'title', element.data 'enableMessage'
				else
					element.data 'status', 1
					icon.attr 'src', icon.attr('src').replace /disabled(Inverse)?.([a-z]{3})$/, 'enabled$1.$2'
					element.attr 'title', element.data 'disableMessage'
					
				$('#timsChatInput').focus()
			
			# Enable fullscreen-mode
			$('#timsChatFullscreen').click (event) ->
				if $(@).data 'status'
					$('html').addClass 'fullscreen'
				else
					$('html').removeClass 'fullscreen'
		###
		# Inserts the new messages.
		#
		# @param	array<object>	messages
		###
		handleMessages: (messages) ->
			# Insert the messages
			for message in messages
				continue if $.wcfIsset 'timsChatMessage' + message.messageID # Prevent problems with race condition
				
				output = @messageTemplate.fetch message
				li = $ '<li></li>'
				li.attr 'id', 'timsChatMessage'+message.messageID
				li.addClass 'timsChatMessage timsChatMessage'+message.type
				li.append output
				
				li.appendTo $ '.timsChatMessageContainer > ul'
		###
		# Refreshes the room-list.
		###
		refreshRoomList: () ->
			console.log 'Refreshing the roomlist'
			$('#toggleRooms a').addClass 'ajaxLoad'
			
			$.ajax $('#toggleRooms a').data('refreshUrl'),
				dataType: 'json'
				type: 'POST'
				success: $.proxy((data, textStatus, jqXHR) ->
					$('#timsChatRoomList li').remove()
					$('#toggleRooms a').removeClass 'ajaxLoad'
					$('#toggleRooms .badge').text data.length
					
					for room in data
						li = $ '<li></li>'
						li.addClass 'activeMenuItem' if room.active
						$('<a href="' + room.link + '">' + room.title + '</a>').addClass('timsChatRoom').appendTo li
						$('#timsChatRoomList ul').append li
					
					console.log 'Found ' + data.length + ' rooms'
				, @)
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
)(jQuery, @, console)
