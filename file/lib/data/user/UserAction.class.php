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
	protected $className = 'wcf\data\user\UserEditor';
	
	public function validatePrepareInvite() {
		// Todo: Proper validation
	}
	
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
	
	public function validateInvite() {
		$this->recipients = (isset($_POST['recipients'])) ? $_POST['recipients'] : null;
		
		if (!$this->recipients) {
			throw new \wcf\system\exception\UserInputException("recipients");
		}
		
		if (WCF::getUser()->chatRoomID) {
			$this->room = \chat\data\room\RoomCache::getInstance()->getRoom(WCF::getUser()->chatRoomID);
		}
		else {
			throw new \wcf\system\exception\UserInputException("roomID");
		}
	}
	
	public function invite() {
		\wcf\system\user\notification\UserNotificationHandler::getInstance()->fireEvent('invited', 'be.bastelstu.chat.room', new \chat\system\user\notification\object\RoomUserNotificationObject($this->room), $this->recipients, [ 'userID' => WCF::getUser()->userID ]);
	}
}
