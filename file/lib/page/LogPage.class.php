<?php
namespace chat\page;
use \chat\data;
use \wcf\system\exception\IllegalLinkException;
use \wcf\system\exception\PermissionDeniedException;
use \wcf\system\WCF;

/**
 * Shows the chat-log.
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2013 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	page
 */
class LogPage extends \wcf\page\AbstractPage {
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
	public $neededPermissions = array('mod.chat.canReadLog');
	
	/**
	 * given roomID
	 * @var integer
	 */
	public $roomID = 0;
	
	/**
	 * given room
	 * @var \chat\data\room\Chat
	 */
	public $room = null;
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
	
		WCF::getTPL()->assign(array(
			'messages' => $this->messages,
			'room' => $this->room,
			'roomID' => $this->roomID
		));
	}
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) $this->roomID = intval($_REQUEST['id']);
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		$cache = data\room\Room::getCache();
		if (!isset($cache[$this->roomID])) throw new IllegalLinkException();
		
		$this->room = $cache[$this->roomID];
		if (!$this->room->canEnter()) throw new \wcf\system\exception\PermissionDeniedException();
		
		// TODO: actually read the correct messages
		$this->messages = data\message\MessageList::getNewestMessages($this->room, 150);
	}
}
