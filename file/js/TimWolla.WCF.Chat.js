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
	TimWolla.WCF.Chat = function(roomID, messageID) { this.init(roomID, messageID); };
	TimWolla.WCF.Chat.prototype = {
		init: function(roomID, messageID) {
			history.replaceState({}, '', 'index.php/Chat/'+roomID);
		}
	};
})(jQuery, document);