<?php
namespace wcf\data\chat\room;

/**
 * Represents a chat room.
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2011 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	timwolla.wcf.chat
 * @subpackage	data.chat.room
 */
class ChatRoom extends \wcf\data\DatabaseObject {
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
	protected static function getCache() {
		if (self::$cache !== null) return;
		CacheHandler::getInstance()->addResource(
			'chatrooms',
			WCF_DIR.'cache/cache.chatrooms.php',
			'wcf\system\cache\builder\ChatRoomCacheBuilder'
		);
		self::$cache = CacheHandler::getInstance()->get('chatrooms');
	}
	
	/**
	 * Returns the name of this chat-room.
	 * 
	 * @return	string
	 */
	public function __tostring() {
		return $this->title;
	}
}
