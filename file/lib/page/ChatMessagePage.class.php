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
		$result = '[';
		foreach ($this->messages as $message) {
			$result .= $message->jsonify().',';
		}
		echo rtrim($result, ',').']';
		exit;
	}
}
