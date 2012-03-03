<?php
namespace wcf\action;
use \wcf\data\chat;
use \wcf\system\WCF;

/**
 * Makes the user leave Tims Chat.
 * 
 * @author 	Tim Düsterhus
 * @copyright	2010-2012 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	timwolla.wcf.chat
 * @subpackage	action
 */
class ChatLeaveAction extends AbstractAction {
	/**
	 * @see	\wcf\action\AbstractAction::$neededModules
	 */
	public $neededModules = array('CHAT_ACTIVE');
	//public $neededPermissions = array('user.chat.canEnter');
	public $room = null;
	public $userData = array();
	
	/**
	 * @see	\wcf\action\IAction::execute()
	 */
	public function execute() {
		parent::execute();
		
		// validate
		if (!WCF::getUser()->userID) {
			throw new IllegalLinkException();
		}
		
		$this->userData['roomID'] = \wcf\util\ChatUtil::readUserData('roomID');
		
		$this->room = chat\room\ChatRoom::getCache()->search($this->userData['roomID']);
		if (!$this->room) throw new \wcf\system\exception\IllegalLinkException();
		if (!$this->room->canEnter()) throw new \wcf\system\exception\PermissionDeniedException();
		
		if (CHAT_DISPLAY_JOIN_LEAVE) {
			$this->userData['color'] = \wcf\util\ChatUtil::readUserData('color');
			
			$messageAction = new chat\message\ChatMessageAction(array(), 'create', array(
				'data' => array(
					'roomID' => $this->room->roomID,
					'sender' => WCF::getUser()->userID,
					'username' => WCF::getUser()->username,
					'time' => TIME_NOW,
					'type' => chat\message\ChatMessage::TYPE_LEAVE,
					'message' => '',
					'color1' => $this->userData['color'][1],
					'color2' => $this->userData['color'][2]
				)
			));
			$messageAction->executeAction();
		}
		
		\wcf\util\ChatUtil::writeUserData(array('roomID' => null));
		
		$this->executed();
		header("HTTP/1.0 204 No Content");
		exit;
	}
}
