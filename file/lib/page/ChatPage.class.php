<?php
namespace wcf\page;
use \wcf\system\WCF;
use \wcf\data\chat;

/**
 * Shows the chat-interface
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2011 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	timwolla.wcf.chat
 * @subpackage	page
 */
class ChatPage extends AbstractPage {
	//public $neededModules = array('CHAT_ACTIVE');
	//public $neededPermissions = array('user.chat.canEnter');
	public $room = null;
	public $roomID = 0;
	public $rooms = array();
	public $smilies = array();
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'room' => $this->room,
			'roomID' => $this->roomID,
			'rooms' => $this->rooms,
			'smilies' => $this->smilies
		));
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		$this->rooms = chat\room\ChatRoom::getCache();
		if ($this->roomID === 0) {
			try {
				$this->rooms->seek(0);
				\wcf\util\HeaderUtil::redirect(\wcf\system\request\LinkHandler::getInstance()->getLink('Chat', array(
					'object' => $this->rooms->search($this->rooms->key())
				)));
				exit;
			}
			catch (\OutOfBoundsException $e) {
				throw new \wcf\system\exception\IllegalLinkException();
			}
		}
		$this->room = $this->rooms->search($this->roomID);
		
		if (!$this->room) throw new \wcf\system\exception\IllegalLinkException();
		
		chat\message\ChatMessageEditor::create(array(
			'roomID' => $this->room->roomID,
			'sender' => WCF::getUser()->userID,
			'username' => WCF::getUser()->username,
			'time' => TIME_NOW,
			'type' => chat\message\ChatMessage::TYPE_JOIN,
			'message' => 'join',
			'enableSmilies' => 0,
			'enableHTML' => 0,
			'color1' => 0xFF0000,
			'color2' => 0x00FF00
		));
		
		$smilies = \wcf\data\smiley\SmileyCache::getInstance()->getSmilies();
		$this->smilies = $smilies[null];
	}
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_GET['id'])) $this->roomID = (int) $_GET['id'];
	}
	
	/**
	 * @see	\wcf\page\IPage::show()
	 */
	public function show() {
		\wcf\system\menu\page\PageMenu::getInstance()->setActiveMenuItem('wcf.header.menu.chat');
		
		// remove index breadcrumb
		WCF::getBreadcrumbs()->remove(0);
		parent::show();
	}
}
