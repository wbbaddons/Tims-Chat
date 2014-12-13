<?php
namespace chat\data\user;
use wcf\system\WCF;

/**
 * User related chat actions.
 * 
 * @author 	Maximilian Mader
 * @copyright	2010-2014 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	data.user
 */
class UserAction extends \wcf\data\AbstractDatabaseObjectAction {
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\user\UserEditor';
	
	/**
	 * Validates invite preparation.
	 */
	public function validatePrepareInvite() {
		WCF::getSession()->checkPermissions(array('user.chat.canInvite'));
		
		if (!WCF::getUser()->chatRoomID) throw new \wcf\system\exception\PermissionDeniedException();
		
		$room = \chat\data\room\RoomCache::getInstance()->getRoom(WCF::getUser()->chatRoomID);
		
		if ($room === null) throw new \wcf\system\exception\IllegalLinkException();
		if (!$room->canEnter()) throw new \wcf\system\exception\PermissionDeniedException();
	}
	
	/**
	 * Prepares invites.
	 */
	public function prepareInvite() {
		$followingList = new \wcf\data\user\follow\UserFollowingList();
		$followingList->getConditionBuilder()->add('user_follow.userID = ?', array(WCF::getUser()->userID));
		$followingList->readObjects();
		$users = $followingList->getObjects();
		
		$json = array();
		foreach ($users as $user) {
			$json[] = array(
				'userID' => $user->userID,
				'username' => $user->username
			);
		}
		
		return array(
			'users' => $json
		);
	}
	
	/**
	 * Validates invites.
	 */
	public function validateInvite() {
		WCF::getSession()->checkPermissions(array('user.chat.canInvite'));
		
		if (!WCF::getUser()->chatRoomID) throw new \wcf\system\exception\PermissionDeniedException();
		
		$this->room = \chat\data\room\RoomCache::getInstance()->getRoom(WCF::getUser()->chatRoomID);
		
		if ($this->room === null) throw new \wcf\system\exception\IllegalLinkException();
		if (!$this->room->canEnter()) throw new \wcf\system\exception\PermissionDeniedException();
		
		if (!isset($this->parameters['recipients'])) throw new \wcf\system\exception\UserInputException("recipients");
	}
	
	/**
	 * Invites users.
	 */
	public function invite() {
		\wcf\system\user\notification\UserNotificationHandler::getInstance()->fireEvent('invited', 'be.bastelstu.chat.room', new \chat\system\user\notification\object\RoomUserNotificationObject($this->room), \wcf\util\ArrayUtil::toIntegerArray($this->parameters['recipients']), array('userID' => WCF::getUser()->userID));
	}
}
