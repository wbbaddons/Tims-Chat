<?php
namespace chat\data\room;

/**
 * Manages the room cache.
 * 
 * @author 	Tim Düsterhus
 * @copyright	2010-2013 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	data.room
 */
class RoomCache extends \wcf\system\SingletonFactory {
	/**
	 * list of cached rooms
	 * @var	array<\chat\data\room\Room>
	 */
	protected $rooms = array();
	
	/**
	 * @see	wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		$this->rooms = \chat\system\cache\builder\RoomCacheBuilder::getInstance()->getData();
	}
	
	/**
	 * Returns a specific room.
	 * 
	 * @param	integer		$roomID
	 * @return	\chat\data\room\Room
	 */
	public function getRoom($roomID) {
		if (isset($this->rooms[$roomID])) {
			return $this->rooms[$roomID];
		}
		
		return null;
	}
	
	/**
	 * Returns all rooms.
	 * 
	 * @return	array<\chat\data\room\Room>
	 */
	public function getRooms() {
		return $this->rooms;
	}
}
