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
			$('.activeMenuItem .chatRoom').parent().removeClass('activeMenuItem');
			target.parent().addClass('activeMenuItem');
			$.ajax(target.attr('href'), {
				dataType: 'json',
				data: { ajax: 1 },
				type: 'POST',
				success: function (data, textStatus, jqXHR) {
					$('#topic').text(data.topic);
					if (data.topic == '') $('#topic').hide();
					else $('#topic').show();
				}
			});
		}
	};
})(jQuery, document);