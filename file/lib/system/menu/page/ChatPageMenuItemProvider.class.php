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
	 * available chat rooms
	 * 
	 * @var	array<\chat\data\room\Room>
	 */
	protected $rooms = null;
	
	/**
	 * Returns the available chat rooms
	 * 
	 * @return	array<\chat\data\room\Room>
	 */
	protected function getRooms() {
		if ($this->rooms !== null) return $this->rooms;
		
		$rooms = \chat\data\room\RoomCache::getInstance()->getRooms();
		
		foreach ($rooms as $room) {
			if ($room->canEnter()) {
				if ($this->room === null) $this->room = $room;
				
				$this->rooms[] = $room;
			}
		}
		
		return $this->rooms;
	}
	
	/**
	 * Hides the button when there is no valid room
	 * 
	 * @see	\wcf\system\menu\page\PageMenuItemProvider::isVisible()
	 */
	public function isVisible() {
		// guests are not supported
		if (!\wcf\system\WCF::getUser()->userID) return false;
		
		$rooms = $this->getRooms();
		
		return !empty($rooms);
	}
	
	/**
	 * Modifies the link to show the Link we would be redirect to.
	 * 
	 * @see	\wcf\system\menu\page\PageMenuItemProvider::getLink()
	 */
	public function getLink() {
		if (CHAT_FORCE_ROOM_SELECT) return parent::getLink();
		
		$this->getRooms();
		
		return \wcf\system\request\LinkHandler::getInstance()->getLink('Chat', array(
			'application' => 'chat',
			'object' => $this->room,
			'forceFrontend' => true
		));
	}
	
	/**
	 * Shows the number of users across all visible rooms.
	 * 
	 * @see	\wcf\system\menu\page\PageMenuItemProvider::getNotifications()
	 */
	public function getNotifications() {
		if (!CHAT_FORCE_ROOM_SELECT) return 0;
		if (!CHAT_ENABLE_MENU_BADGE) return 0;
		
		$rooms = $this->getRooms();
		return array_reduce($rooms, function ($carry, $room) { return $carry + count($room->getUsers()); }, 0);
	}
}
