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
	/**
	 * The new and unseen messages.
	 * 
	 * @var array<\wcf\data\chat\message\ChatMessage>
	 */
	public $messages = array();
	
	/**
	 * @see \wcf\page\AbstractPage::$neededModules
	 */
	public $neededModules = array('CHAT_ACTIVE');
	
	/**
	 * @see \wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('user.chat.canEnter');
	
	/**
	 * The room the user joined.
	 * 
	 * @var \wcf\data\chat\room\ChatRoom
	 */
	public $room = null;
	
	/**
	 * All the users that are currently in the room $this->room.
	 * 
	 * @var array<\wcf\data\user\User>
	 */
	public $users = array();
	
	/**
	 * @see \wcf\page\AbstractPage::$useTemplate
	 */
	public $useTemplate = false;
	
	/**
	 * @see	\wcf\page\Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->readRoom();
		$this->readMessages();
		$this->users = $this->room->getUsers();
		
		$deadUsers = \wcf\util\ChatUtil::getDiedUsers();
		foreach ($deadUsers as $deadUser) {
			if (!$deadUser) continue;
			
			$user = new \wcf\data\user\User($deadUser['userID']);
			if (CHAT_DISPLAY_JOIN_LEAVE) {
				$userData['color'] = \wcf\util\ChatUtil::readUserData('color', $user);
			
				$messageAction = new chat\message\ChatMessageAction(array(), 'create', array(
					'data' => array(
						'roomID' => $deadUser['roomID'],
						'sender' => $user->userID,
						'username' => $user->username,
						'time' => TIME_NOW,
						'type' => chat\message\ChatMessage::TYPE_LEAVE,
						'message' => '',
						'color1' => $userData['color'][1],
						'color2' => $userData['color'][2]
					)
				));
				$messageAction->executeAction();
			}
			\wcf\util\ChatUtil::writeUserData(array('roomID' => null), $user);
		}
	}
	
	/**
	 * Fetches the new messages
	 */
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
		
		\wcf\util\ChatUtil::writeUserData(array(
			'lastSeen' => $row['messageID'],
			'lastActivity' => TIME_NOW
		));
	}
	
	/**
	 * Initializes the room databaseobject.
	 */
	public function readRoom() {
		$roomID = \wcf\util\ChatUtil::readUserData('roomID');
		
		$this->room = chat\room\ChatRoom::getCache()->search($roomID);
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
				'awayStatus' => $user->awayStatus,
				'suspended' => $user->suspended
			);
		}
		
		echo \wcf\util\JSON::encode($json);
		exit;
	}
}
