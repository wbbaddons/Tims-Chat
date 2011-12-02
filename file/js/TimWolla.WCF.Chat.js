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
		}
	};
})(jQuery, document);