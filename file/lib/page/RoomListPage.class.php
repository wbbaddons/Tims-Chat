<?php
namespace chat\page;
use \chat\data;
use \wcf\system\cache\CacheHandler;
use \wcf\system\exception\IllegalLinkException;
use \wcf\system\WCF;

/**
 * Outputs roomlist.
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2013 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	page
 */
class RoomListPage extends \wcf\page\AbstractPage {
	/**
	 * @see wcf\page\AbstractPage::$loginRequired
	 */
	public $loginRequired = true;
	
	/**
	 * @see \wcf\page\AbstractPage::$neededModules
	 */
	public $neededModules = array('CHAT_ACTIVE');
	
	/**
	 * @see \wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array();
	
	/**
	 * the room the user current is in
	 * @var \chat\data\room\Room
	 */
	public $room = null;
	
	/**
	 * all rooms in the current installation
	 * @var array<\chat\data\room\Room>
	 */
	public $rooms = array();
	
	/**
	 * @see \wcf\page\AbstractPage::$useTemplate
	 */
	public $useTemplate = false;
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->rooms = data\room\Room::getCache();
		
		$roomID = \chat\util\ChatUtil::readUserData('roomID');
		if (!isset($this->rooms[$roomID])) throw new IllegalLinkException();
		$this->room = $this->rooms[$roomID];
	}
	
	/**
	 * @see	\wcf\page\IPage::show()
	 */
	public function show() {
		parent::show();
		
		@header('Content-type: application/json');
		$json = array();
		foreach ($this->rooms as $room) {
			if (!$room->canEnter()) continue;
			
			$json[] = array(
				'title' => WCF::getLanguage()->get($room->title),
				'link' => \wcf\system\request\LinkHandler::getInstance()->getLink('Chat', array(
					'application' => 'chat',
					'object' => $room
				)),
				'active' => $room->roomID == $this->room->roomID
			);
		}
		echo \wcf\util\JSON::encode($json);
		exit;
	}
}
