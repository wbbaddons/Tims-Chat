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
 * @package	be.bastelstu.wcf.chat
 * @subpackage	page
 */
class ChatMessagePage extends AbstractPage {
	public $messages = array();
	public $neededModules = array('CHAT_ACTIVE');
	public $neededPermissions = array('user.chat.canEnter');
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
		$this->users = $this->room->getUsers();
	}
	
	public function readMessages() {
		$this->messages = chat\message\ChatMessageList::getMessagesSince($this->room, \wcf\util\ChatUtil::readUserData('lastSeen'));
		
		// update last seen message
		if (count($this->messages)) {
			$lastSeen = $this->messages[count($this->messages)-1]->messageID;
			\wcf\util\ChatUtil::writeUserData(array('lastSeen' => $lastSeen));
		}
	}
	
	public function readRoom() {
		$this->roomID = \wcf\util\ChatUtil::readUserData('roomID');
		
		$this->room = chat\room\ChatRoom::getCache()->search($this->roomID);
		if (!$this->room) throw new \wcf\system\exception\IllegalLinkException();
		if (!$this->room->canEnter()) throw new \wcf\system\exception\PermissionDeniedException();
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
		// enable gzip compression
		if (HTTP_ENABLE_GZIP && HTTP_GZIP_LEVEL > 0 && HTTP_GZIP_LEVEL < 10 && !defined('HTTP_DISABLE_GZIP')) {
			\wcf\util\HeaderUtil::compressOutput();
		}
		
		$json = array('users' => array(), 'messages' => array());
		
		foreach ($this->messages as $message) {
			$json['messages'][] = $message->jsonify(true);
		}
		foreach ($this->users as $user) {
			$json['users'][] = array(
				'userID' => $user->userID,
				'username' => $user->username,
				'awayStatus' => $user->awayStatus
			);
		}
		
		echo \wcf\util\JSON::encode($json);
		exit;
	}
}
