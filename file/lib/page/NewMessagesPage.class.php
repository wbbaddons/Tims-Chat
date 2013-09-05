<?php
namespace chat\page;
use \chat\data;
use \wcf\system\exception\IllegalLinkException;
use \wcf\system\WCF;

/**
 * Loads new messages.
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2013 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	page
 */
class NewMessagesPage extends \wcf\page\AbstractPage {
	/**
	 * @see wcf\page\AbstractPage::$loginRequired
	 */
	public $loginRequired = true;
	
	/**
	 * The new and unseen messages.
	 * 
	 * @var array<\wcf\data\chat\message\ChatMessage>
	 */
	public $messages = array();
	
	/**
	 * @see \wcf\page\AbstractPage::$neededModules
	 */
	public $neededModules = array('MODULE_CHAT');
	
	/**
	 * @see \wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array();
	
	/**
	 * The room the user joined.
	 * 
	 * @var \chat\data\room\Room
	 */
	public $room = null;
	
	/**
	 * All the users that are currently in the room $this->room.
	 * 
	 * @var array<\wcf\data\user\UserProfile>
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
		
		$roomAction = new data\room\RoomAction(array(), 'removeDeadUsers');
		$roomAction->executeAction();
	}
	
	/**
	 * Fetches the new messages
	 */
	public function readMessages() {
		$this->messages = data\message\ViewableMessageList::getMessagesSince($this->room, WCF::getUser()->chatLastSeen);
		
		// update last seen message
		$sql = "SELECT
				MAX(messageID)
			FROM
				chat".WCF_N."_message";
		$stmt = WCF::getDB()->prepareStatement($sql);
		$stmt->execute();
		
		$editor = new \wcf\data\user\UserEditor(WCF::getUser());
		$editor->update(array(
			'chatLastSeen' => $stmt->fetchColumn(),
			'chatLastActivity' => TIME_NOW
		));
	}
	
	/**
	 * Initializes the room databaseobject.
	 */
	public function readRoom() {
		$this->room = \chat\data\room\RoomCache::getInstance()->getRoom(WCF::getUser()->chatRoomID);
		if (!$this->room) throw new IllegalLinkException();
		if (!$this->room->canEnter()) throw new \wcf\system\exception\PermissionDeniedException();
	}
	
	/**
	 * @see	\wcf\page\IPage::show()
	 */
	public function show() {
		parent::show();
		
		@header('Content-type: application/json');
		\wcf\util\HeaderUtil::sendNoCacheHeaders();
		
		$json = array('users' => array(), 'messages' => array());
		
		foreach ($this->messages as $message) {
			$json['messages'][] = $message->jsonify(true);
		}
		
		\wcf\system\user\storage\UserStorageHandler::getInstance()->loadStorage(array_keys($this->users));
		
		foreach ($this->users as $user) {
			$json['users'][] = array(
				'userID' => (int) $user->userID,
				'username' => $user->username,
				'awayStatus' => $user->chatAway,
				'suspended' => (boolean) !$this->room->canWrite($user->getDecoratedObject()),
				'avatar' => array(
					16 => $user->getAvatar()->getImageTag(16),
					24 => $user->getAvatar()->getImageTag(24),
					32 => $user->getAvatar()->getImageTag(32),
					48 => $user->getAvatar()->getImageTag(48)
				),
				'link' => \wcf\system\request\LinkHandler::getInstance()->getLink('User', array(
					'object' => $user->getDecoratedObject()
				))
			);
		}
		
		if (ENABLE_BENCHMARK) {
			$b = \wcf\system\benchmark\Benchmark::getInstance();
			$items = array();
			if (ENABLE_DEBUG_MODE) {
				foreach ($b->getItems() as $item) {
					$items[] = array(
						'text' => $item['text'],
						'use' => $item['use'],
						'trace' => $item['trace']
					);
				}
			}
			
			$json['benchmark'] = array(
				'time' => $b->getExecutionTime(),
				'queryTime' => $b->getQueryExecutionTime(),
				'queryPercent' => $b->getQueryExecutionTime() / $b->getExecutionTime(),
				'items' => $items
			);
		}
		
		echo \wcf\util\JSON::encode($json);
		exit;
	}
}
