<?php
namespace chat\system\menu\page;

/**
 * PageMenuItemProvider for chat.
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2013 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	system.menu.page
 */
class ChatPageMenuItemProvider extends \wcf\system\menu\page\DefaultPageMenuItemProvider {
	protected $room = null;
	
	/**
	 * Hides the button when there is no valid room
	 *
	 * @see	\wcf\system\menu\page\PageMenuItemProvider::isVisible()
	 */
	public function isVisible() {
		// guests are not supported
		if (!\wcf\system\WCF::getUser()->userID) return false;
		
		$cache = \chat\data\room\Room::getCache();
		
		foreach ($cache as $this->room) {
			if ($this->room->canEnter()) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Modifies the link to show the Link we would be redirect to.
	 * 
	 * @see	\wcf\system\menu\page\PageMenuItemProvider::getLink()
	 */
	public function getLink() {
		return \wcf\system\request\LinkHandler::getInstance()->getLink('Chat', array(
			'application' => 'chat',
			'object' => $this->room
		));
	}
}
