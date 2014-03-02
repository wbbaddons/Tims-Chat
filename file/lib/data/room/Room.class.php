<?php
namespace chat\data\room;
use \chat\data\suspension\Suspension;
use \wcf\system\WCF;

/**
 * Represents a chat room.
 * 
 * @author 	Tim Düsterhus
 * @copyright	2010-2014 Tim Düsterhus
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
	 * cached users
	 * 
	 * @var	array<\wcf\data\user\UserProfile>
	 */
	protected static $users = null;
	
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
		$user = new \wcf\data\user\UserProfile($user);
		
		if ($this->isPermanent && $user->getPermission('admin.chat.canManageSuspensions')) return true;
		if ($this->isPermanent && $user->getPermission('mod.chat.canGban')) return true;
		
		$ph = new \chat\system\permission\PermissionHandler($user->getDecoratedObject());
		if ($ph->getPermission($this, 'mod.canAlwaysEnter')) return true;
		if ($ph->getPermission($this, 'mod.canBan')) return true;
		
		if (!$ph->getPermission($this, 'user.canEnter')) return false;
		
		$suspensions = Suspension::getSuspensionsForUser($user->getDecoratedObject());
		// room suspension
		if (isset($suspensions[$this->roomID][Suspension::TYPE_BAN])) {
			if ($suspensions[$this->roomID][Suspension::TYPE_BAN]->isValid()) {
				return false;
			}
		}
		
		// global suspension
		if (isset($suspensions[null][Suspension::TYPE_BAN])) {
			if ($suspensions[null][Suspension::TYPE_BAN]->isValid()) {
				return false;
			}
		}
		
		return true;
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
		$user = new \wcf\data\user\UserProfile($user);
		
		if ($user->getPermission('admin.chat.canManageSuspensions')) return true;
		if ($user->getPermission('mod.chat.canGmute')) return true;
		
		$ph = new \chat\system\permission\PermissionHandler($user->getDecoratedObject());
		if ($ph->getPermission($this, 'mod.canAlwaysWrite')) return true;
		if ($ph->getPermission($this, 'mod.canMute')) return true;
		
		if (!$ph->getPermission($this, 'user.canWrite')) return false;
		
		$suspensions = Suspension::getSuspensionsForUser($user->getDecoratedObject());
		// room suspension
		if (isset($suspensions[$this->roomID][Suspension::TYPE_MUTE])) {
			if ($suspensions[$this->roomID][Suspension::TYPE_MUTE]->isValid()) {
				return false;
			}
		}
		
		// global suspension
		if (isset($suspensions[null][Suspension::TYPE_MUTE])) {
			if ($suspensions[null][Suspension::TYPE_MUTE]->isValid()) {
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Returns whether the user is allowed to mute other users in this room.
	 *
	 * @return	boolean
	 */
	public function canMute() {
		if (WCF::getSession()->getPermission('admin.chat.canManageSuspensions')) return true;
		if (WCF::getSession()->getPermission('mod.chat.canGmute')) return true;
		
		$ph = new \chat\system\permission\PermissionHandler();
		return $ph->getPermission($this, 'mod.canMute');
	}
	
	/**
	 * Returns whether the user is allowed to ban other users in this room.
	 *
	 * @return	boolean
	 */
	public function canBan() {
		if (WCF::getSession()->getPermission('admin.chat.canManageSuspensions')) return true;
		if (WCF::getSession()->getPermission('mod.chat.canGban')) return true;
		
		$ph = new \chat\system\permission\PermissionHandler();
		return $ph->getPermission($this, 'mod.canBan');
	}
	
	/**
	 * Returns the ID of this chatroom.
	 * 
	 * @see	\wcf\system\request\IRouteController
	 */
	public function getID() {
		return $this->roomID;
	}
	
	/**
	 * Returns the name of this chatroom.
	 * 
	 * @see	\wcf\system\request\IRouteController
	 */
	public function getTitle() {
		return \wcf\system\WCF::getLanguage()->get($this->title);
	}
	
	/**
	 * Returns the topic of this chat room
	 * 
	 * @return	string
	 */
	public function getTopic() {
		return \wcf\system\WCF::getLanguage()->get($this->topic);
	}
	
	/**
	 * Returns the users that are currently active in this room.
	 * 
	 * @return	array<\wcf\data\user\UserProfile>
	 */
	public function getUsers() {
		if (self::$users === null) {
			$userList = new \wcf\data\user\UserProfileList();
			$userList->getConditionBuilder()->add('user_table.chatRoomID IS NOT NULL', array());
			
			$userList->readObjects();
			$users = $userList->getObjects();
			
			foreach ($users as $user) {
				if (!isset(self::$users[$user->chatRoomID])) self::$users[$user->chatRoomID] = array();
				self::$users[$user->chatRoomID][] = $user;
			}
		}
		if (!isset(self::$users[$this->roomID])) self::$users[$this->roomID] = array();
		
		return self::$users[$this->roomID];
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
