<?php
namespace wcf\page;
use \wcf\data\chat;
use \wcf\system\WCF;

/**
 * Loads new messages.
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2011 Tim Düsterhus
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
	 * Reads room data.
	 */
	public function readData() {
		parent::readData();
		$this->roomID = \wcf\util\ChatUtil::readUserData('roomID');
		
		$this->room = chat\room\ChatRoom::getCache()->search($this->roomID);
		if (!$this->room) throw new \wcf\system\exception\IllegalLinkException();
		if (!$this->room->canEnter()) throw new \wcf\system\exception\PermissionDeniedException();
		
		$this->messages = chat\message\ChatMessageList::getMessagesSince($this->room, \wcf\util\ChatUtil::readUserData('lastSeen'));
		$stmt = WCF::getDB()->prepareStatement("SELECT max(messageID) as messageID FROM wcf".WCF_N."_chat_message");
		$stmt->execute();
		$row = $stmt->fetchArray();
		\wcf\util\ChatUtil::writeUserData(array('lastSeen' => $row['messageID']));
		
		$sql = "SELECT userID FROM wcf".WCF_N."_user_storage WHERE field = 'roomID' AND packageID = 16 AND fieldValue = ".intval($this->roomID);
		$stmt = WCF::getDB()->prepareStatement($sql);
		$stmt->execute();
		while ($row = $stmt->fetchArray()) $userIDs[] = $row['userID'];
		
		$sql = "SELECT 	u.*
			FROM 	wcf".WCF_N."_user u
			WHERE userID IN (".rtrim(str_repeat('?,', count($userIDs)), ',').")
			ORDER BY u.username ASC";
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
