<?php
namespace wcf\system\menu\page;
use \wcf\data\chat\room\ChatRoom;

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
	protected $room = null;
	
	/**
	 * Hides the button when there is no valid room
	 *
	 * @see \wcf\system\menu\page\PageMenuItemProvider::isVisible()
	 */
	public function isVisible() {
		// guests are not supported
		if (!\wcf\system\WCF::getUser()->userID) return false;
		
		try {
			$cache = ChatRoom::getCache();
			$cache->seek(0);
			$this->room = $cache->search($cache->key());
			return true;
		}
		catch (\OutOfBoundsException $e) {
			return false;
		}
	}
	
	/**
	 * Modifies the link to show the Link we would be redirect to.
	 * 
	 * @see \wcf\system\menu\page\PageMenuItemProvider::getLink()
	 */
	public function getLink() {
		return \wcf\system\request\LinkHandler::getInstance()->getLink('Chat', array(
			'object' => $this->room
		));
	}
}
