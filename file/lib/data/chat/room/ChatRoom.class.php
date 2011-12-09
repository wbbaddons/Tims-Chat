<?php
namespace wcf\data\chat\room;
use \wcf\system\cache\CacheHandler;

/**
 * Represents a chat room.
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2011 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	timwolla.wcf.chat
 * @subpackage	data.chat.room
 */
class ChatRoom extends \wcf\data\DatabaseObject implements \wcf\system\request\IRouteController {
	/**
	 * @see	wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'chat_room';
	
	/**
	 * @see	wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'roomID';
		
	/**
	 * Caches rooms.
	 * 
	 * @var	array<wcf\data\chat\room\ChatRoom>
	 */
	protected static $cache = null;
	
	/**
	 * Loads the room cache.
	 */
	public static function getCache() {
		if (self::$cache === null) {
			CacheHandler::getInstance()->addResource(
				'chatrooms',
				WCF_DIR.'cache/cache.chatrooms.php',
				'wcf\system\cache\builder\ChatRoomCacheBuilder'
			);
			self::$cache = CacheHandler::getInstance()->get('chatrooms');
		}
		
		return self::$cache;
	}
	
	/**
	 * Clears the room cache.
	 */
	public static function clearCache() {
		self::getCache();
		CacheHandler::getInstance()->clearResource('chatrooms');
	}
	
	/**
	 * @see	\wcf\data\chat\room\ChatRoom::getTitle();
	 */
	public function __toString() {
		return $this->getTitle();
	}
	
	/**
	 * Returns the name of this chat-room.
	 * 
	 * @see	\wcf\system\request\IRouteController
	 */
	public function getTitle() {
		return $this->title;
	}
	
	/**
	 * Returns the ID of this chat-room.
	 *
	 * @see	\wcf\system\request\RRouteHandler
	 */
	public function getID() {
		return $this->roomID;
	}
}
