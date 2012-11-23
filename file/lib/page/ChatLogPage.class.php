<?php
namespace wcf\page;
use \wcf\data\chat;
use \wcf\system\exception\IllegalLinkException;
use \wcf\system\exception\PermissionDeniedException;
use \wcf\system\WCF;

/**
 * Shows the chat-log.
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2012 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.wcf.chat
 * @subpackage	page
 */
class ChatLogPage extends AbstractPage {
	/**
	 * @see wcf\page\AbstractPage::$loginRequired
	 */
	public $loginRequired = true;
	
	/**
	 * TODO: comment this
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
	 * given roomID
	 * @var integer
	 */
	public $roomID = 0;
	
	/**
	 * given room
	 * @var \wcf\data\chat\room\ChatRoom
	 */
	public $room = null;
	
	/**
	 * all rooms in the current installation
	 * @var array<\wcf\data\chat\room\ChatRoom>
	 */
	public $rooms = array();
	
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
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
	
		WCF::getTPL()->assign(array(
			'messages' => $this->messages,
			'room' => $this->room,
			'roomID' => $this->roomID,
			'rooms' => $this->rooms,
			'sidebarCollapsed' => \wcf\system\user\collapsible\content\UserCollapsibleContentHandler::getInstance()->isCollapsed('com.woltlab.wcf.collapsibleSidebar', 'be.bastelstu.wcf.chat.ChatLogPage'),
			'sidebarName' => 'be.bastelstu.wcf.chat.ChatLogPage'
		));
	}
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) $this->roomID = (int) $_REQUEST['id'];
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		$cache = chat\room\ChatRoom::getCache();
		if (!isset($cache[$this->roomID])) throw new IllegalLinkException();
		
		$this->room = $cache[$this->roomID];
		if (!$this->room->canEnter()) throw new \wcf\system\exception\PermissionDeniedException();
		$ph = new \wcf\system\chat\permission\ChatPermissionHandler();
		if (!$ph->getPermission($this->room, 'mod.canReadLog')) throw new \wcf\system\exception\PermissionDeniedException();
		
		// TODO: actually read the correct messages
		$this->messages = chat\message\ChatMessageList::getNewestMessages($this->room, 150);
	}
}
