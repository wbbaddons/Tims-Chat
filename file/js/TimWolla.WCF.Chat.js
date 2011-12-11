/**
 * TimWolla.WCF.Chat
 * 
 * @author	Tim Düsterhus
 * @copyright	2010-2011 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	timwolla.wcf.chat
 */
if (typeof TimWolla == 'undefined') var TimWolla = {};
if (typeof TimWolla.WCF == 'undefined') TimWolla.WCF = {};
	

(function ($, document) {
	TimWolla.WCF.Chat = {
		titleTemplate: null,
		messageTemplate: null,
		init: function(roomID, messageID) {
			this.bindEvents();
			$('#chatBox').css('background-color', $('div.main').css('background-color'));
			$('#chatRoomContent').width($('#chatBox').width() - 400);
		},
		bindEvents: function () {
			$('.smiley').click($.proxy(function (event) {
				this.insertText($(event.target).attr('alt'));
			}, this));

			var chatRoomContent = $('#chatRoomContent');
			var chatBox = $('#chatBox');
			$(window).resize(function() {
				chatRoomContent.width(chatBox.width() - 400);
			});
			
			// $(window).bind('beforeunload', function() {
				// return false;
			// });
			
			$('.chatRoom').click($.proxy(function (event) {
				if (typeof window.history.replaceState != 'undefined') {
					event.preventDefault();
					this.changeRoom($(event.target));
				}
			}, this));
			
			$('.chatUser .chatUserLink').click($.proxy(function (event) {
				event.preventDefault();
				this.toggleUserMenu($(event.target));
			}, this));
			$('#chatForm').submit($.proxy(function (event) {
				event.preventDefault();
				$(event.target).find('input[type=image]').attr('src', WCF.Icon.get('wcf.icon.loading'));
			}, this));
		},
		changeRoom: function (target) {
			window.history.replaceState({}, '', target.attr('href'));
			
			// actually change the room
			$.ajax(target.attr('href'), {
				dataType: 'json',
				data: { ajax: 1 },
				type: 'POST',
				success: $.proxy(function (data, textStatus, jqXHR) {
					this.loading = false;
					
					target.parent().removeClass('ajaxLoad');
					
					// mark as active;
					$('.activeMenuItem .chatRoom').parent().removeClass('activeMenuItem');
					target.parent().addClass('activeMenuItem');
					
					// set new topic
					if (data.topic == '') {
						if (data.topic == '' && $('#topic').text().trim() == '') return;
						
						$('#topic').wcfBlindOut('vertical', function () {
							$(this).text('');
						});
					}
					else {
						if ($('#topic').text().trim() != '') $('#topic').text(data.topic);
						else {
							$('#topic').text(data.topic);
							$('#topic').wcfBlindIn();
						}
					}
					
					// set page-title
					$('title').text(this.titleTemplate.fetch(data));
				}, this),
				error: function() {
					// reload page to change the room the old fashion-way
					// inclusive the error-message :)
					window.location.reload(true);
				},
				beforeSend: $.proxy(function () {
					if (this.loading || target.parent().hasClass('activeMenuItem')) return false;
					
					this.loading = true;

					target.parent().addClass('ajaxLoad');
				}, this)
			});
		},
		handleMessages: function (messages) {
			for (message in messages) {
				message = messages[message];
				output = this.messageTemplate.fetch(message);
				
				li = $('<li></li>');
				li.addClass('chatMessage chatMessage'+message.type);
				if (message.sender == WCF.User.userID) li.addClass('ownMessage');
				li.append(output);
				
				$('.chatMessageConainer ul').append(li);
			}
			$('.chatMessageConainer').animate({scrollTop: $('.chatMessageConainer ul').height()}, 10000);
		},
		insertText: function (text) {
			// TODO: Add options here
			var input = $('#chatInput');
			input.val(input.val() + ' ' + text + ' ');
		},
		toggleUserMenu: function (target) {
			liUserID = '#' + target.parent().parent().attr('id');
			if ($(liUserID).hasClass('activeMenuItem')) {
				$(liUserID + ' .chatUserMenu').wcfBlindOut('vertical', function () {
					$(liUserID).removeClass('activeMenuItem');
				});
			}
			else {
				$(liUserID).addClass('activeMenuItem');
				$(liUserID + ' .chatUserMenu').wcfBlindIn();
			}
		}
	};
})(jQuery, document);