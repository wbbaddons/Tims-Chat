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
		titleTemplate: '',
		init: function(roomID, messageID) {
			this.bindEvents();
		},
		bindEvents: function() {
			$('.smiley').click(function(event) {
				alert($(event.target).attr('alt'));
			});
			
			$(window).bind('beforeunload', function() {
				return false;
			});
			
			$('.chatRoom').click($.proxy(function (event) {
				if (typeof window.history.replaceState != 'undefined') {
					event.preventDefault();
					this.changeRoom($(event.target));
				}
			}, this));
		},
		changeRoom: function(target) {
			window.history.replaceState({}, '', target.attr('href'));
			
			// mark as active;
			$('.activeMenuItem .chatRoom').parent().removeClass('activeMenuItem');
			target.parent().addClass('activeMenuItem');
				
			// actually change the room
			$.ajax(target.attr('href'), {
				dataType: 'json',
				data: { ajax: 1 },
				type: 'POST',
				success: $.proxy(function (data, textStatus, jqXHR) {
					// set new topic
					if (data.topic == '') {
						$('#topic').wcfBlindOut('vertical', function() {
							$('#topic').text('');
						});
					} else {
						if($('#topic').text() != "") $('#topic').text(data.topic);
						else {
							$('#topic').text(data.topic);
							$('#topic').wcfBlindIn();
						}
					}
					
					// set page-title
					$('title').text(this.titleTemplate.fetch(data));
				}, this)
			});
		}
	};
})(jQuery, document);