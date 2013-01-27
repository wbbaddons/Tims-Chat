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
class ChatMessagePage extends \wcf\page\AbstractPage {
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
	public $neededModules = array('CHAT_ACTIVE');
	
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
	 * @var array<\wcf\data\user\User>
	 */
	public $users = array();
	
	/**
	 * @see \wcf\page\AbstractPage::$useTemplate
	 */
	public $useTemplate = false;
	
	/**
	 * shortcut for the active request
	 * @see wcf\system\request\Request::getRequestObject()
	 */
	public $request = null;
	
	/**
	 * Disallows direct access.
	 * 
	 * @see wcf\page\IPage::__run()
	 */
	public function __run() {
		if (($this->request = \wcf\system\request\RequestHandler::getInstance()->getActiveRequest()->getRequestObject()) === $this) throw new IllegalLinkException();
		
		parent::__run();
	}
	
	/**
	 * @see	\wcf\page\Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->readRoom();
		$this->readMessages();
		$this->users = $this->room->getUsers();
		
		$deadUsers = \chat\util\ChatUtil::getDiedUsers();
		foreach ($deadUsers as $deadUser) {
			if (!$deadUser) continue;
			
			$user = new \wcf\data\user\User($deadUser['userID']);
			if (CHAT_DISPLAY_JOIN_LEAVE) {
				$userData['color'] = \chat\util\ChatUtil::readUserData('color', $user);
			
				$messageAction = new data\message\MessageAction(array(), 'create', array(
					'data' => array(
						'roomID' => $deadUser['roomID'],
						'sender' => $user->userID,
						'username' => $user->username,
						'time' => TIME_NOW,
						'type' => data\message\Message::TYPE_LEAVE,
						'message' => '',
						'color1' => $userData['color'][1],
						'color2' => $userData['color'][2]
					)
				));
				$messageAction->executeAction();
			}
			\chat\util\ChatUtil::writeUserData(array('roomID' => null), $user);
		}
	}
	
	/**
	 * Fetches the new messages
	 */
	public function readMessages() {
		$this->messages = data\message\MessageList::getMessagesSince($this->room, \chat\util\ChatUtil::readUserData('lastSeen'));
		
		// update last seen message
		$sql = "SELECT
				MAX(messageID)
			FROM
				chat".WCF_N."_message";
		$stmt = WCF::getDB()->prepareStatement($sql);
		$stmt->execute();
		
		\chat\util\ChatUtil::writeUserData(array(
			'lastSeen' => $stmt->fetchColumn(),
			'lastActivity' => TIME_NOW
		));
	}
	
	/**
	 * Initializes the room databaseobject.
	 */
	public function readRoom() {
		$roomID = \chat\util\ChatUtil::readUserData('roomID');
		$cache = data\room\Room::getCache();
		if (!isset($cache[$roomID])) throw new IllegalLinkException();
		
		$this->room = $cache[$roomID];
		if (!$this->room->canEnter()) throw new \wcf\system\exception\PermissionDeniedException();
	}
	
	/**
	 * @see	\wcf\page\IPage::show()
	 */
	public function show() {
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
				'userID' => (int) $user->userID,
				'username' => $user->username,
				'awayStatus' => $user->awayStatus,
				'suspended' => (boolean) !$this->room->canWrite($user)
			);
		}
		
		if (ENABLE_BENCHMARK) {
			$b = \wcf\system\benchmark\Benchmark::getInstance();
			$items = array();
			if (ENABLE_DEBUG_MODE) {
				foreach ($b->getItems() as $item) {
					$items[] = array('text' => $item['text'], 'use' => $item['use']);
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
