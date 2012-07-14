<?php
namespace wcf\page;
use \wcf\data\chat;
use \wcf\system\cache\CacheHandler;
use \wcf\system\WCF;

/**
 * Shows the chat-interface
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2012 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.wcf.chat
 * @subpackage	page
 */
class ChatPage extends AbstractPage {
	/**
	 * The version of this installation of Tims Chat 3.
	 * 
	 * @var string
	 */
	public $chatVersion = '';
	
	/**
	 * @see \wcf\page\AbstractPage::$neededModules
	 */
	public $neededModules = array('CHAT_ACTIVE');
	
	/**
	 * @see \wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('user.chat.canEnter');
	
	/**
	 * The last X messages for the current room.
	 * 
	 * @var array<\wcf\data\chat\message\ChatMessage>
	 */
	public $newestMessages = array();
	
	/**
	 * The current room.
	 * 
	 * @var \wcf\data\chat\room\ChatRoom
	 */
	public $room = null;
	
	/**
	 * The given roomID.
	 * 
	 * @var integer
	 */
	public $roomID = 0;
	
	/**
	 * List of accessible rooms.
	 * 
	 * @var \wcf\data\chat\room\ChatRoomList
	 */
	public $rooms = array();
	
	/**
	 * List of smilies in the default category.
	 * 
	 * @var array<\wcf\data\smiley\Smiley>
	 * @see \wcf\data\smiley\SmileyCache
	 */
	public $defaultSmilies = array();
	
	/**
	 * List of all smiley categories.
	 * 
	 * @var array<\wcf\data\smiley\SmileyCategory>
	 * @see \wcf\data\smiley\SmileyCache
	 */
	public $smileyCategories = array();
	
	/**
	 * Values read from the UserStorage of the current user.
	 * 
	 * @var array
	 */
	public $userData = array();
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();

		WCF::getTPL()->assign(array(
			'chatVersion' => $this->chatVersion,
			'newestMessages' => $this->newestMessages,
			'room' => $this->room,
			'roomID' => $this->roomID,
			'rooms' => $this->rooms,
			'defaultSmilies' => $this->defaultSmilies,
			'smileyCategories' => $this->smileyCategories
		));
	}
	
	/**
	 * Reads chat-version. Used to avoid caching of JS-File when Tims Chat is updated.
	 */
	public function readChatVersion() {
		CacheHandler::getInstance()->addResource(
			'packages',
			WCF_DIR.'cache/cache.packages.php',
			'wcf\system\cache\builder\PackageCacheBuilder'
		);
		$packages = CacheHandler::getInstance()->get('packages');
		foreach ($packages as $package) {
			if ($package->package != \wcf\util\ChatUtil::PACKAGE_IDENTIFIER) continue;
			$this->chatVersion = $package->packageVersion;
			return;
		}
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->readRoom();
		$this->userData['color'] = \wcf\util\ChatUtil::readUserData('color');
		\wcf\util\ChatUtil::writeUserData(array(
			'roomID' => $this->room->roomID,
			'away' => null,
			'lastActivity' => TIME_NOW
		));
		
		if (CHAT_DISPLAY_JOIN_LEAVE) {
			$messageAction = new chat\message\ChatMessageAction(array(), 'create', array(
				'data' => array(
					'roomID' => $this->room->roomID,
					'sender' => WCF::getUser()->userID,
					'username' => WCF::getUser()->username,
					'time' => TIME_NOW,
					'type' => chat\message\ChatMessage::TYPE_JOIN,
					'message' => '',
					'color1' => $this->userData['color'][1],
					'color2' => $this->userData['color'][2]
				)
			));
			$messageAction->executeAction();
			$messageAction->getReturnValues();
		}
		
		$this->newestMessages = chat\message\ChatMessageList::getNewestMessages($this->room, CHAT_LASTMESSAGES);
		try {
			\wcf\util\ChatUtil::writeUserData(array('lastSeen' => end($this->newestMessages)->messageID));
		}
		catch (\wcf\system\exception\SystemException $e) {
			\wcf\util\ChatUtil::writeUserData(array('lastSeen' => 0));
		}
		
		$smileyCategories = \wcf\data\smiley\SmileyCache::getInstance()->getCategories();
		
		foreach($smileyCategories as $category) {
			if(!$category->disabled) $this->smileyCategories[] = $category;
		}
		
		$this->defaultSmilies = \wcf\data\smiley\SmileyCache::getInstance()->getCategorySmilies();
		$this->readChatVersion();
	}
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		switch ($this->action) {
			case 'Message':
				$page = new ChatMessagePage();
				$page->__run();
				exit;
			case 'Log':
				exit;
			case 'RefreshRoomList':
				$page = new ChatRefreshRoomListPage();
				$page->__run();
				exit;
			case 'Send':
				$form = new \wcf\form\ChatForm();
				$form->__run();
				exit;
			case 'Leave':
				$action = new \wcf\action\ChatLeaveAction();
				$action->__run();
				exit;
			case 'Copyright':
				$page = new ChatCopyrightPage();
				$page->__run();
				exit;
		}
		
		if (isset($_REQUEST['id'])) $this->roomID = (int) $_REQUEST['id'];
		if (isset($_REQUEST['ajax'])) $this->useTemplate = false;
	}
	
	/**
	 * Reads room data.
	 */
	public function readRoom() {
		$this->rooms = chat\room\ChatRoom::getCache();
		
		if ($this->roomID === 0) {
			// no room given
			try {
				// redirect to first chat-room
				$this->rooms->seek(0);
				\wcf\util\HeaderUtil::redirect(\wcf\system\request\LinkHandler::getInstance()->getLink('Chat', array(
					'object' => $this->rooms->current()
				)));
				exit;
			}
			catch (\OutOfBoundsException $e) {
				// no valid room found
				throw new \wcf\system\exception\IllegalLinkException();
			}
		}
		
		$this->room = $this->rooms->search($this->roomID);
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
		
		\wcf\system\menu\page\PageMenu::getInstance()->setActiveMenuItem('wcf.header.menu.chat');
		
		// remove index breadcrumb
		WCF::getBreadcrumbs()->remove(0);
		
		parent::show();
		
		// break if not using ajax
		if ($this->useTemplate) exit;
		@header('Content-type: application/json');
		
		$messages = array();
		foreach ($this->newestMessages as $message) $messages[] = $message->jsonify(true);
		echo \wcf\util\JSON::encode(array(
			'title' => $this->room->getTitle(),
			'topic' => WCF::getLanguage()->get($this->room->topic),
			'messages' => $messages
		));
		exit;
	}
}
