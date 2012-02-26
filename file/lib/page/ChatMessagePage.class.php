<?php
namespace wcf\page;
use \wcf\data\chat;
use \wcf\system\WCF;

/**
 * Loads new messages.
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2012 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	timwolla.wcf.chat
 * @subpackage	page
 */
class ChatMessagePage extends AbstractPage {
	public $messages = array();
	public $neededModules = array('CHAT_ACTIVE');
	//public $neededPermissions = array('user.chat.canEnter');
	public $room = null;
	public $roomID = 0;
	public $users = array();
	public $useTemplate = false;
	
	/**
	 * @see	\wcf\page\Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->readRoom();
		$this->readMessages();
		$this->readUsers();
	}
	
	public function readMessages() {
		$this->messages = chat\message\ChatMessageList::getMessagesSince($this->room, \wcf\util\ChatUtil::readUserData('lastSeen'));
		
		// update last seen message
		$sql = "SELECT
				max(messageID) as messageID
			FROM 
				wcf".WCF_N."_chat_message";
		$stmt = WCF::getDB()->prepareStatement($sql);
		$stmt->execute();
		$row = $stmt->fetchArray();
		\wcf\util\ChatUtil::writeUserData(array('lastSeen' => $row['messageID']));
	}
	
	public function readRoom() {
		$this->roomID = \wcf\util\ChatUtil::readUserData('roomID');
		
		$this->room = chat\room\ChatRoom::getCache()->search($this->roomID);
		if (!$this->room) throw new \wcf\system\exception\IllegalLinkException();
		if (!$this->room->canEnter()) throw new \wcf\system\exception\PermissionDeniedException();
	}
	
	public function readUsers() {
		$packageID = \wcf\system\package\PackageDependencyHandler::getPackageID('timwolla.wcf.chat');
		
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
		$this->users = $stmt->fetchObjects('\wcf\data\user\User');
	}
	
	/**
	 * @see	\wcf\page\IPage::show()
	 */
	public function show() {
		// guests are not supported
		if (!WCF::getUser()->userID) {
			throw new \wcf\system\exception\PermissionDeniedException();
		}
		
		parent::show();
		
		@header('Content-type: application/json');
		$json = array('users' => array(), 'messages' => array());
		
		foreach ($this->messages as $message) {
			$json['messages'][] = $message->jsonify(true);
		}
		foreach ($this->users as $user) {
			$json['users'][] = array(
				'userID' => $user->userID,
				'username' => $user->username
			);
		}
		
		echo \wcf\util\JSON::encode($json);
		exit;
	}
}
