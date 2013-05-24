<?php
namespace chat\data\room;
use \chat\data\suspension\Suspension;
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
		if (!$user->userID) return false;
		
		$ph = new \chat\system\permission\PermissionHandler($user);
		$suspensions = Suspension::getSuspensionsForUser($user);
		
		$canEnter = $ph->getPermission($this, 'user.canEnter');
		// room suspension
		if ($canEnter && isset($suspensions[$this->roomID][Suspension::TYPE_BAN])) {
			if ($suspensions[$this->roomID][Suspension::TYPE_BAN]->isValid()) {
				$canEnter = false;
			}
		}
		
		// global suspension
		if ($canEnter && isset($suspensions[null][Suspension::TYPE_BAN])) {
			if ($suspensions[null][Suspension::TYPE_BAN]->isValid()) {
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
		if (!$user->userID) return false;
		
		$ph = new \chat\system\permission\PermissionHandler($user);
		$suspensions = Suspension::getSuspensionsForUser($user);
		
		$canWrite = $ph->getPermission($this, 'user.canWrite');
		// room suspension
		if ($canWrite && isset($suspensions[$this->roomID][Suspension::TYPE_MUTE])) {
			if ($suspensions[$this->roomID][Suspension::TYPE_MUTE]->isValid()) {
				$canWrite = false;
			}
		}
		
		// global suspension
		if ($canWrite && isset($suspensions[null][Suspension::TYPE_MUTE])) {
			if ($suspensions[null][Suspension::TYPE_MUTE]->isValid()) {
				$canWrite = false;
			}
		}
		
		return $canWrite || $ph->getPermission($this, 'mod.canAlwaysWrite');
	}
	
	/**
	 * Loads the room cache.
	 */
	public static function getCache() {
		return \chat\system\cache\builder\RoomCacheBuilder::getInstance()->getData();
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
	 * Returns the number of users currently active in this room.
	 *
	 * @return	integer
	 */
	public function countUsers() {
		$sql = "SELECT
				COUNT(*)
			FROM
				wcf".WCF_N."_user
			WHERE
				chatRoomID = ?";
		$stmt = WCF::getDB()->prepareStatement($sql);
		$stmt->execute(array($this->roomID));
	
		return $stmt->fetchColumn();
	}
	
	/**
	 * Returns the users that are currently active in this room.
	 * 
	 * @return	\wcf\data\user\UserProfileList
	 */
	public function getUsers() {
		$userList = new \wcf\data\user\UserProfileList();
		$userList->getConditionBuilder()->add('user_table.chatRoomID = ?', array($this->roomID));
		
		$userList->readObjects();
		
		return $userList;
	}
	
	/**
	 * Returns the users that "timed out".
	 * 
	 * @return	\wcf\data\user\UserList
	 */
	public static function getDeadUsers() {
		if (\wcf\system\nodePush\NodePushHandler::getInstance()->isEnabled()) {
			$time = TIME_NOW - 180;
		}
		else {
			$time = TIME_NOW;
		}
		
		$userList = new \wcf\data\user\UserList();
		$userList->getConditionBuilder()->add('user_table.chatRoomID IS NOT NULL', array());
		$userList->getConditionBuilder()->add('user_table.chatLastActivity < ?', array($time - 30));
		
		$userList->readObjects();
		
		return $userList;
	}
}
