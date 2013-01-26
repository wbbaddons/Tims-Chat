<?php
namespace chat\system\cache\builder;

/**
 * Caches all chat rooms.
 * 
 * @author	Tim Düsterhus
 * @copyright	2010-2013 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	system.cache.builder
 */
class RoomCacheBuilder implements \wcf\system\cache\builder\ICacheBuilder {
	/**
	 * @see	\wcf\system\cache\ICacheBuilder::getData()
	 */
	public function getData(array $cacheResource) {
		// get all chat rooms
		$roomList = new \chat\data\room\RoomList();
		$roomList->sqlOrderBy = "room.position";
		$roomList->readObjects();
		
		return $roomList->getObjects();
	}
}
