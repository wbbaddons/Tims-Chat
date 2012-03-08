<?php
namespace wcf\data\chat\room;
use \wcf\system\cache\CacheHandler;
use \wcf\system\WCF;

/**
 * Represents a chat room.
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2012 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	timwolla.wcf.chat
 * @subpackage	data.chat.room
 */
class ChatRoom extends \wcf\data\DatabaseObject implements \wcf\system\request\IRouteController {
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'chat_room';
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'roomID';
		
	/**
	 * Caches rooms.
	 * 
	 * @var	array<wcf\data\chat\room\ChatRoom>
	 */
	protected static $cache = null;
	
	/**
	 * @see	\wcf\data\chat\room\ChatRoom::getTitle();
	 */
	public function __toString() {
		return $this->getTitle();
	}
	
	/**
	 * Returns the number of users currently active in this room.
	 * 
	 * @return	integer
	 */
	public function countUsers() {
		$packageID = \wcf\util\ChatUtil::getPackageID();

		$sql = "SELECT
				count(*) as count
			FROM
				wcf".WCF_N."_user_storage 
			WHERE
					field = 'roomID' 
				AND	packageID = ".intval($packageID)."
				AND 	fieldValue = ".intval($this->roomID);
		$stmt = WCF::getDB()->prepareStatement($sql);
		$stmt->execute();
		$row = $stmt->fetchArray();
		
		return $row['count'];
	}
	
	/**
	 * Loads the room cache.
	 */
	public static function getCache() {
		if (self::$cache === null) {
			CacheHandler::getInstance()->addResource(
				'chatrooms',
				WCF_DIR.'cache/cache.chatrooms.php',
				'\wcf\system\cache\builder\ChatRoomCacheBuilder'
			);
			self::$cache = CacheHandler::getInstance()->get('chatrooms');
		}
		
		return self::$cache;
	}
	
	/**
	 * Returns the ID of this chat-room.
	 *
	 * @see	\wcf\system\request\IRouteController
	 */
	public function getID() {
		return $this->roomID;
	}
	
	/**
	 * Returns the name of this chat-room.
	 * 
	 * @see	\wcf\system\request\IRouteController
	 */
	public function getTitle() {
		return \wcf\system\WCF::getLanguage()->get($this->title);
	}
	
	/**
	 * Returns the users that are currently active in this room.
	 * 
	 * @return	array<\wcf\data\user\User>
	 */
	public function getUsers() {
		$packageID = \wcf\util\ChatUtil::getPackageID();

		$sql = "SELECT
				userID
			FROM
				wcf".WCF_N."_user_storage 
			WHERE
					field = 'roomID' 
				AND	packageID = ".intval($packageID)."
				AND 	fieldValue = ".intval($this->roomID);
		$stmt = WCF::getDB()->prepareStatement($sql);
		$stmt->execute();
		$userIDs = array();
		while ($row = $stmt->fetchArray()) $userIDs[] = $row['userID'];

		if (!count($userIDs)) return;

		$sql = "SELECT
				*
			FROM
				wcf".WCF_N."_user
			WHERE
				userID IN (".rtrim(str_repeat('?,', count($userIDs)), ',').")
			ORDER BY
				username ASC";
		$stmt = WCF::getDB()->prepareStatement($sql);
		$stmt->execute($userIDs);
		return $stmt->fetchObjects('\wcf\data\user\User');
	}
	
	/**
	 * Returns whether the user is allowed to enter the room
	 *
	 * @return	boolean
	 */
	public function canEnter() {
		$ph = \wcf\system\chat\permission\ChatPermissionHandler::getInstance();
		
		return $ph->getPermission($this, 'user.canEnter') || $ph->getPermission($this, 'mod.canAlwaysEnter');
	}
}
