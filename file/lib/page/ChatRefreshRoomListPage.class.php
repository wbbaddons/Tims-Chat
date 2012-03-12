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
class ChatRefreshRoomListPage extends AbstractPage {
	public $neededModules = array('CHAT_ACTIVE');
	//public $neededPermissions = array('user.chat.canEnter');
	public $room = null;
	public $roomID = 0;
	public $rooms = array();
	public $useTemplate = false;
	
	/**
	 * Reads room data.
	 */
	public function readData() {
		parent::readData();
		$this->roomID = \wcf\util\ChatUtil::readUserData('roomID');
		$this->rooms = chat\room\ChatRoom::getCache();
		
		$this->room = $this->rooms->search($this->roomID);
		if (!$this->room) throw new \wcf\system\exception\IllegalLinkException();
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
		$json = array();
		foreach ($this->rooms as $room) {
			if (!$room->canEnter()) continue;
			$json[] = array(
				'title' => WCF::getLanguage()->get($room->title),
				'link' => \wcf\system\request\LinkHandler::getInstance()->getLink('Chat', array(
					'object' => $room
				)),
				'active' => $room->roomID == $this->room->roomID
			);
		}
		echo \wcf\util\JSON::encode($json);
		exit;
	}
}
