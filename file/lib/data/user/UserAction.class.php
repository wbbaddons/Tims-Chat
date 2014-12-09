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
		// Todo: Proper validation
	}
	
	/**
	 * Prepares invites.
	 */
	public function prepareInvite() {
		$followingList = new \wcf\data\user\follow\UserFollowingList();
		$followingList->getConditionBuilder()->add('user_follow.userID = ?', array(WCF::getUser()->userID));
		$followingList->readObjects();
		$users = $followingList->getObjects();
		
		WCF::getTPL()->assign(array(
			'users' => $users
		));
		
		return array(
			'template' => WCF::getTPL()->fetch('userInviteDialog', 'chat')
		);
	}
	
	/**
	 * Validates invites.
	 */
	public function validateInvite() {
		if (!isset($this->parameters['recipients'])) throw new \wcf\system\exception\UserInputException("recipients");
		
		if (WCF::getUser()->chatRoomID) {
			$this->room = \chat\data\room\RoomCache::getInstance()->getRoom(WCF::getUser()->chatRoomID);
		}
		else {
			throw new \wcf\system\exception\UserInputException("roomID");
		}
	}
	
	/**
	 * Invites users.
	 */
	public function invite() {
		\wcf\system\user\notification\UserNotificationHandler::getInstance()->fireEvent('invited', 'be.bastelstu.chat.room', new \chat\system\user\notification\object\RoomUserNotificationObject($this->room), $this->parameters['recipients'], array('userID' => WCF::getUser()->userID));
	}
}
