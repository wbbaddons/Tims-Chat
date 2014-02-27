<?php
namespace chat\system\cache\builder;

/**
 * Caches all chat rooms.
 * 
 * @author	Tim Düsterhus
 * @copyright	2010-2014 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	system.cache.builder
 */
class RoomCacheBuilder extends \wcf\system\cache\builder\AbstractCacheBuilder {
	/**
	 * @see	\wcf\system\cache\AbstractCacheBuilder::rebuild()
	 */
	public function rebuild(array $parameters) {
		// get all chat rooms
		$roomList = new \chat\data\room\RoomList();
		$roomList->sqlOrderBy = "room.showOrder";
		$roomList->readObjects();
		
		return $roomList->getObjects();
	}
}
