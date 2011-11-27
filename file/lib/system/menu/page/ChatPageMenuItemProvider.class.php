<?php
namespace wcf\system\menu\page;

/**
 * PageMenuItemProvider for chat.
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2011 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	timwolla.wcf.chat
 * @subpackage	system.menu.page
 */
class ChatPageMenuItemProvider extends DefaultPageMenuItemProvider {
	/**
	 * Hides the button when there is no valid room
	 *
	 * @see wcf\system\menu\page\PageMenuItemProvider::isVisible()
	 */
	public function isVisible() {
		// guests are not supported
		if (!WCF::getUser()->userID) return false;
		
		try {
			\wcf\data\chat\room\ChatRoom::getCache()->seek(0);
			return true;
		}
		catch (\OutOfBoundsException $e) {
			return false;
		}
	}
}
