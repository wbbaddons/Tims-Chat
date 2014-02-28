<?php
namespace chat\system\menu\page;

/**
 * PageMenuItemProvider for chat.
 * 
 * @author 	Tim Düsterhus
 * @copyright	2010-2014 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	system.menu.page
 */
class ChatPageMenuItemProvider extends \wcf\system\menu\page\DefaultPageMenuItemProvider {
	/**
	 * room that the menu item points to
	 * 
	 * @var \chat\data\room\Room
	 */
	protected $room = null;
	
	/**
	 * Hides the button when there is no valid room
	 * 
	 * @see	\wcf\system\menu\page\PageMenuItemProvider::isVisible()
	 */
	public function isVisible() {
		// guests are not supported
		if (!\wcf\system\WCF::getUser()->userID) return false;
		
		$rooms = \chat\data\room\RoomCache::getInstance()->getRooms();
		
		foreach ($rooms as $this->room) {
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
		if (CHAT_FORCE_ROOM_SELECT) return parent::getLink();
		
		return \wcf\system\request\LinkHandler::getInstance()->getLink('Chat', array(
			'application' => 'chat',
			'object' => $this->room,
			'forceFrontend' => true
		));
	}
}
