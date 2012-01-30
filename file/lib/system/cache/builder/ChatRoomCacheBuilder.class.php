<?php
namespace wcf\system\cache\builder;

/**
 * Caches all chat rooms.
 * 
 * @author	Tim Düsterhus
 * @copyright	2010-2012 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	timwolla.wcf.chat
 * @subpackage	system.cache.builder
 */
class ChatRoomCacheBuilder implements ICacheBuilder {
	/**
	 * @see wcf\system\cache\ICacheBuilder::getData()
	 */
	public function getData(array $cacheResource) {
		// get all chat rooms
		$roomList = new \wcf\data\chat\room\ChatRoomList();
		$roomList->sqlOrderBy = "chat_room.position";
		$roomList->sqlLimit = 0;
		$roomList->readObjects();
		
		return $roomList;
	}
}
