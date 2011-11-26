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
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'roomID' => $this->roomID
		));
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		$this->rooms = chat\room\ChatRoom::getCache();
		if (isset($this->rooms[$this->roomID])) {
			$this->room = $this->rooms[$this->roomID];
		}
		else {
			throw new \wcf\system\exception\IllegalLinkException();
		}
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
		parent::show();
	}
}
