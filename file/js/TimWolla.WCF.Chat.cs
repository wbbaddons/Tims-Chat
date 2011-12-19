###
TimWolla.WCF.Chat
  	
@author  Tim Düsterhus
@copyright  2010-2011 Tim Düsterhus
@license  Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
@package  timwolla.wcf.chat
###

TimWolla ?= {}
TimWolla.WCF ?= {}

(($) ->
	TimWolla.WCF.Chat =
		titleTemplate: null
		messageTemplate: null
		init: (roomID, messageID) ->
			this.bindEvents()
			this.refreshRoomList()
			new WCF.PeriodicalExecuter $.proxy(this.refreshRoomList, this), 10e3
			
			$('#chatInput').focus()
		bindEvents: () ->
			$('.smiley').click $.proxy (event) ->
				this.insertText ' ' + $(event.target).attr('alt') + ' '
			, this
	
			$('.chatSidebarTabs li').click $.proxy (event) ->
				event.preventDefault()
				this.toggleSidebarContents $ event.target
			, this
	
			$('.chatUser .chatUserLink').click $.proxy (event) ->
				event.preventDefault()
				this.toggleUserMenu $ event.target
			, this
			
			$('#chatForm').submit $.proxy (event) ->
				event.preventDefault()
				this.submit $ event.target
			, this
			
			$('#chatClear').click (event) ->
				event.preventDefault()
				$('.chatMessage').remove()
				$('#chatInput').focus()
				
			$('.chatToggle').click (event) ->
				element = $ this
				icon = element.find 'img'
				if element.data('status') == 1
					element.data 'status', 0
					icon.attr 'src', icon.attr('src').replace /enabled(\d?).([a-z]{3})$/, 'disabled$1.$2'
					element.attr 'title', element.data 'enableMessage'
				else
					element.data 'status', 1
					icon.attr 'src', icon.attr('src').replace /disabled(\d?).([a-z]{3})$/, 'enabled$1.$2'
					element.attr 'title', element.data 'disableMessage'
		changeRoom: (target) ->
			window.history.replaceState {}, '', target.attr('href')
				
			$.ajax target.attr('href'), 
				dataType: 'json'
				data: 
					ajax: 1
				type: 'POST'
				success: $.proxy((data, textStatus, jqXHR) ->
					this.loading = false
					target.parent().removeClass 'ajaxLoad'
					
					# mark as active
					$('.activeMenuItem .chatRoom').parent().removeClass 'activeMenuItem'
					target.parent().addClass 'activeMenuItem'
				
					# set new topic
					if data.topic == ''
						return if $('#topic').text().trim() == ''
						
						$('#topic').wcfBlindOut 'vertical', () ->
							$(this).text ''
					else
						$('#topic').text data.topic
						$('#topic').wcfBlindIn() if $('#topic').text().trim() != ''
					
					$('title').text this.titleTemplate.fetch(data)
				, this)
				error: () ->
					# reload page to change the room the old fashion-way
					# inclusive the error-message :)
					window.location.reload true
				beforeSend: $.proxy(() ->
					return false if this.loading or target.parent().hasClass 'activeMenuItem'
					
					this.loading = true
					target.parent().addClass 'ajaxLoad'
				, this)
		getMessages: () ->
		handleMessages: (messages) ->
			for message in messages
				output = this.messageTemplate.fetch message
				li = $ '<li></li>'
				li.addClass 'chatMessage chatMessage'+message.type
				li.addClass 'ownMessage' if message.sender == WCF.User.userID
				li.append output
				
				li.appendTo $ '.chatMessageContainer ul'
			$('.chatMessageContainer').animate 
				scrollTop: $('.chatMessageContainer ul').height()
			, 1000
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
		refreshRoomList: () ->
			$('.chatRoom').unbind 'click'
			$('#toggleRooms a').addClass 'ajaxLoad'
			
			$.ajax $('#toggleRooms a').data('refreshUrl'),
				dataType: 'json'
				type: 'POST'
				success: $.proxy((data, textStatus, jqXHR) ->
					$('#chatRoomList li').remove()
					$('#toggleRooms a').removeClass 'ajaxLoad'
					for room in data
						li = $ '<li></li>'
						li.addClass 'activeMenuItem' if room.active
						$('<a href="' + room.link + '">' + room.title + '</a>').addClass('chatRoom').appendTo li
						$('#chatRoomList ul').append li
					$('.chatRoom').click $.proxy (event) ->
						return if typeof window.history.replaceState == 'undefined'
						event.preventDefault()
						this.changeRoom $ event.target
					, this
				, this)
		submit: (target) ->
			# break if input contains only whitespace
			return false if $('#chatInput').val().trim().length == 0
			
			$.ajax $('#chatForm').attr('action'), 
				data:
					text: $('#chatInput').val()
				type: 'POST',
				beforeSend: (jqXHR) ->
					$('#chatInput').addClass 'ajaxLoad'
				success: $.proxy((data, textStatus, jqXHR) ->
					this.getMessages()
					$('#chatInput').val('').focus()
					$('#chatInput').keyup()
				, this)
				complete: () ->
					$('#chatInput').removeClass 'ajaxLoad'
		toggleSidebarContents: (target) ->
			return if target.parent().hasClass 'active'
			
			if target.parent().attr('id') == 'toggleUsers'
				$('#toggleUsers').addClass 'active'
				$('#toggleRooms').removeClass 'active'
				
				$('#chatRoomList').hide()
				$('#chatUserList').show()
			else if target.parent().attr('id') == 'toggleRooms'
				$('#toggleRooms').addClass 'active'
				$('#toggleUsers').removeClass 'active'
				
				$('#chatUserList').hide()
				$('#chatRoomList').show()
		toggleUserMenu: (target) ->
			liUserID = '#' + target.parent().parent().attr 'id'
			
			if $(liUserID).hasClass 'activeMenuItem'
				$(liUserID + ' .chatUserMenu').wcfBlindOut 'vertical', () ->
					$(liUserID).removeClass 'activeMenuItem'
			else
				$(liUserID).addClass 'activeMenuItem'
				$(liUserID + ' .chatUserMenu').wcfBlindIn()
)(jQuery)
