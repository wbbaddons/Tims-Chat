<?php
namespace chat\page;
use \chat\data;
use \wcf\system\exception;
use \wcf\system\WCF;

/**
 * Shows the chat-interface
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2013 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	page
 */
class ChatPage extends \wcf\page\AbstractPage {
	/**
	 * @see wcf\page\AbstractPage::$loginRequired
	 */
	public $loginRequired = true;
	
	/**
	 * @see \wcf\page\AbstractPage::$neededModules
	 */
	public $neededModules = array('MODULE_CHAT');
	
	/**
	 * @see \wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array();
	
	/**
	 * The current room.
	 * 
	 * @var \chat\data\room\Room
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
	 * @var \chat\data\room\RoomList
	 */
	public $rooms = array();
	
	/**
	 * List of installed commands.
	 * 
	 * @var array<string>
	 */
	public $commands = array();
	
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
	 * attachment handler
	 * @see	\wcf\system\attachment\AttachmentHandler
	 */
	public $attachmentHandler = null;
	
	/**
	 * @see wcf\page\AbstractPage::$enableTracking
	 */
	public $enableTracking = true;
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		$reflection = new \ReflectionClass('\chat\data\message\Message');
		
		WCF::getTPL()->assign(array(
			'room' => $this->room,
			'roomID' => $this->roomID,
			'rooms' => $this->rooms,
			'commands' => $this->commands,
			'messageTypes' => $reflection->getConstants(),
			'defaultSmilies' => $this->defaultSmilies,
			'smileyCategories' => $this->smileyCategories,
			'attachmentHandler' => $this->attachmentHandler,
			'sidebarCollapsed' => \wcf\system\user\collapsible\content\UserCollapsibleContentHandler::getInstance()->isCollapsed('com.woltlab.wcf.collapsibleSidebar', 'be.bastelstu.chat.ChatPage'),
			'sidebarName' => 'be.bastelstu.chat.ChatPage'
		));
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->readRoom();
		$this->readCommands();
		
		// get default smilies
		if (MODULE_SMILEY) {
			$this->smileyCategories = \wcf\data\smiley\SmileyCache::getInstance()->getCategories();
			foreach ($this->smileyCategories as $index => $category) {
				$category->loadSmilies();
				
				// remove empty categories
				if (!count($category) || $category->isDisabled) {
					unset($this->smileyCategories[$index]);
				}
			}
			
			$firstCategory = reset($this->smileyCategories);
			if ($firstCategory) {
				$this->defaultSmilies = \wcf\data\smiley\SmileyCache::getInstance()->getCategorySmilies($firstCategory->categoryID ?: null);
			}
		}
		
		if (MODULE_ATTACHMENT) {
			$this->attachmentHandler = new \wcf\system\attachment\AttachmentHandler('be.bastelstu.chat.message', 0, '', 0);
		}
	}
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) $this->roomID = (int) $_REQUEST['id'];
	}
	
	/**
	 * Reads installed commands.
	 */
	public function readCommands() {
		$regex = new \wcf\system\Regex('Command.class.php$');
		$directory = \wcf\util\DirectoryUtil::getInstance(CHAT_DIR.'lib/system/command/commands/', false);
		$files = $directory->getFiles(SORT_ASC, $regex);
		
		foreach ($files as $file) {
			$command = $regex->replace(basename($file), '');
			if ($command == 'Plain') continue;
			$this->commands[] = mb_strtolower($command);
		}
		
		$this->commands = array_merge($this->commands, array_keys(\chat\system\command\CommandHandler::getAliasMap()));
		sort($this->commands);
	}
	
	/**
	 * Reads room data.
	 */
	public function readRoom() {
		$this->rooms = data\room\RoomCache::getInstance()->getRooms();
		
		if ($this->roomID === 0) {
			// no room given
			if (CHAT_FORCE_ROOM_SELECT) {
				return;
			}
			else {
				$room = reset($this->rooms);
				if ($room === null) {
					// no valid room found
					throw new exception\IllegalLinkException();
				}
				// redirect to first chat-room
				\wcf\util\HeaderUtil::redirect(\wcf\system\request\LinkHandler::getInstance()->getLink('Chat', array(
					'object' => $room
				)));
				exit;
			}
		}
		
		if (!isset($this->rooms[$this->roomID])) throw new exception\IllegalLinkException();
		$this->room = $this->rooms[$this->roomID];
		if (!$this->room->canEnter()) throw new exception\PermissionDeniedException();
	}
	
	/**
	 * @see	\wcf\page\IPage::show()
	 */
	public function show() {
		\wcf\system\menu\page\PageMenu::getInstance()->setActiveMenuItem('chat.header.menu.chat');
		
		// remove index breadcrumb
		WCF::getBreadcrumbs()->remove(0);
		
		parent::show();
	}
	
	/**
	 * @see	wcf\page\ITrackablePage::getObjectType()
	 */
	public function getObjectType() {
		return 'be.bastelstu.chat.room';
	}
	
	/**
	 * @see	wcf\page\ITrackablePage::getObjectID()
	 */
	public function getObjectID() {
		if ($this->room === null) return 0;
		
		return $this->room->roomID;
	}
}
