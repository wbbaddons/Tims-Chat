<?php
namespace chat\data\room;
use \chat\data\suspension\Suspension;
use \wcf\system\cache\CacheHandler;
use \wcf\system\WCF;

/**
 * Represents a chat room.
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2013 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	data.room
 */
class Room extends \chat\data\CHATDatabaseObject implements \wcf\system\request\IRouteController {
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'room';
	
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
	 * Returns whether the user is allowed to enter the room.
	 * 
	 * @param	\wcf\data\user\User	$user
	 * @return	boolean
	 */
	public function canEnter(\wcf\data\user\User $user = null) {
		if ($user === null) $user = WCF::getUser();
		
		$ph = new \chat\system\permission\PermissionHandler($user);
		$suspensions = Suspension::getSuspensionsForUser($user);
		
		$canEnter = $ph->getPermission($this, 'user.canEnter');
		// room suspension
		if ($canEnter && isset($suspensions[$this->roomID][Suspension::TYPE_BAN])) {
			if ($suspensions[$this->roomID][Suspension::TYPE_BAN]['time'] > TIME_NOW) {
				$canEnter = false;
			}
		}
		
		// global suspension
		if ($canEnter && isset($suspensions[null][Suspension::TYPE_BAN])) {
			if ($suspensions[null][Suspension::TYPE_BAN]['time'] > TIME_NOW) {
				$canEnter = false;
			}
		}
		
		return $canEnter || $ph->getPermission($this, 'mod.canAlwaysEnter');
	}
	
	/**
	 * Returns whether the user is allowed to write messages in this room.
	 *
	 * @param	\wcf\data\user\User	$user
	 * @return	boolean
	 */
	public function canWrite(\wcf\data\user\User $user = null) {
		if ($user === null) $user = WCF::getUser();
		
		$ph = new \chat\system\permission\PermissionHandler($user);
		$suspensions = Suspension::getSuspensionsForUser($user);
		
		$canWrite = $ph->getPermission($this, 'user.canWrite');
		// room suspension
		if ($canWrite && isset($suspensions[$this->roomID][Suspension::TYPE_MUTE])) {
			if ($suspensions[$this->roomID][Suspension::TYPE_MUTE]['time'] > TIME_NOW) {
				$canWrite = false;
			}
		}
		
		// global suspension
		if ($canWrite && isset($suspensions[null][Suspension::TYPE_MUTE])) {
			if ($suspensions[null][Suspension::TYPE_MUTE]['time'] > TIME_NOW) {
				$canWrite = false;
			}
		}
		
		return $canWrite || $ph->getPermission($this, 'mod.canAlwaysWrite');
	}
	
	/**
	 * Returns the number of users currently active in this room.
	 * 
	 * @return	integer
	 */
	public function countUsers() {
		$sql = "SELECT
				COUNT(*)
			FROM
				wcf".WCF_N."_user_storage 
			WHERE
					field = ?
				AND 	fieldValue = ?";
		$stmt = WCF::getDB()->prepareStatement($sql);
		$stmt->execute(array('roomID', $this->roomID));
		
		return $stmt->fetchColumn();
	}
	
	/**
	 * Loads the room cache.
	 */
	public static function getCache() {
		if (self::$cache === null) {
			CacheHandler::getInstance()->addResource(
				'chatRooms',
				CHAT_DIR.'cache/cache.rooms.php',
				'\chat\system\cache\builder\RoomCacheBuilder'
			);
			self::$cache = CacheHandler::getInstance()->get('chatRooms');
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
		$sql = "SELECT
				userID
			FROM
				wcf".WCF_N."_user_storage 
			WHERE
					field = ?
				AND 	fieldValue = ?";
		$stmt = WCF::getDB()->prepareStatement($sql);
		$stmt->execute(array('roomID', $this->roomID));
		$userIDs = array();
		while ($userID = $stmt->fetchColumn()) $userIDs[] = $userID;
		
		if (empty($userIDs)) return array();
		
		$sql = "SELECT
				u.*,
				st.fieldValue AS awayStatus
			FROM
				wcf".WCF_N."_user u
			LEFT JOIN
				wcf".WCF_N."_user_storage st
				ON (
						u.userID = st.userID 
					AND	st.field = ?
				)
			WHERE
				u.userID IN (".rtrim(str_repeat('?,', count($userIDs)), ',').")
			ORDER BY
				u.username ASC";
		$stmt = WCF::getDB()->prepareStatement($sql);
		array_unshift($userIDs, 'away');
		$stmt->execute($userIDs);
		
		return $stmt->fetchObjects('\wcf\data\user\User');
	}
}
