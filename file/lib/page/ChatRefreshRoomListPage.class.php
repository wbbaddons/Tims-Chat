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
	/**
	 * @see \wcf\page\AbstractPage::$neededModules
	 */
	public $neededModules = array('CHAT_ACTIVE');
	
	/**
	 * @see \wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('user.chat.canEnter');
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
		
		$this->rooms = chat\room\ChatRoom::getCache();
		
		$roomID = \wcf\util\ChatUtil::readUserData('roomID');
		$room = $this->rooms->search($roomID);
		if (!$room) throw new \wcf\system\exception\IllegalLinkException();
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
