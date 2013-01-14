<?php
namespace wcf\system\chat\command\commands;
use \wcf\data\chat\suspension;
use \wcf\data\user\User;
use \wcf\system\WCF;
use \wcf\util\ChatUtil;

/**
 * Mutes a user.
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2012 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.wcf.chat
 * @subpackage	system.chat.command.commands
 */
class MuteCommand extends \wcf\system\chat\command\AbstractRestrictedCommand {
	public $user = null;
	public $time = 0;
	public $suspensionAction = null;
	public $link = '';
	public $fail = false;
	public $room = null;
	
	public function __construct(\wcf\system\chat\command\CommandHandler $commandHandler) {
		parent::__construct($commandHandler);
		
		$parameters = $commandHandler->getParameters();
		if (($comma = strpos($parameters, ',')) !== false) {
			$username = substr($parameters, 0, $comma);
			$this->time = ChatUtil::timeModifier(substr($parameters, $comma + 1));
		}
		else {
			throw new \wcf\system\chat\command\NotFoundException();
		}
		
		$this->user = User::getUserByUsername($username);
		if (!$this->user->userID) throw new \wcf\system\chat\command\UserNotFoundException($username);
		
		$color = ChatUtil::readUserData('color', $this->user);
		$profile = \wcf\system\request\LinkHandler::getInstance()->getLink('User', array(
			'object' => $this->user
		));
		$this->link = '<span class="userLink" data-user-id="'.$this->user->userID.'" />';
		
		$this->executeAction();
		
		$this->didInit();
	}
	
	public function executeAction() {
		if ($suspension = suspension\ChatSuspension::getSuspensionByUserRoomAndType($this->user, $this->room, suspension\ChatSuspension::TYPE_MUTE)) {
			if ($suspension->time > TIME_NOW + $this->time) {
				$this->fail = true;
				return;
			}
			
			$editor = new suspension\ChatSuspensionEditor($suspension);
			$editor->delete();
		}
		
		$this->suspensionAction = new suspension\ChatSuspensionAction(array(), 'create', array(
			'data' => array(
				'userID' => $this->user->userID,
				'roomID' => ChatUtil::readUserData('roomID'),
				'type' => suspension\ChatSuspension::TYPE_MUTE,
				'time' => TIME_NOW + $this->time
			)
		));
		$this->suspensionAction->executeAction();
	}
	
	/**
	 * @see	\wcf\system\chat\command\IRestrictedChatCommand::checkPermission()
	 */
	public function checkPermission() {
		parent::checkPermission();
		
		$this->room = \wcf\system\request\RequestHandler::getInstance()->getActiveRequest()->getRequestObject()->request->room;
		$ph = new \wcf\system\chat\permission\ChatPermissionHandler();
		if (!$ph->getPermission($this->room, 'mod.can'.str_replace(array('wcf\system\chat\command\commands\\', 'Command'), '', get_class($this)))) throw new \wcf\system\exception\PermissionDeniedException();
	}
	
	/**
	 * @see	wcf\system\chat\command\ICommand::getReceiver()
	 */
	public function getReceiver() {
		if ($this->fail) return WCF::getUser()->userID;
		
		return parent::getReceiver();
	}
	
	/**
	 * @see	\wcf\system\chat\command\ICommand::getType()
	 */
	public function getType() {
		if ($this->fail) return \wcf\data\chat\message\ChatMessage::TYPE_INFORMATION;
		return \wcf\data\chat\message\ChatMessage::TYPE_MODERATE;
	}
	
	/**
	 * @see	\wcf\system\chat\command\ICommand::getMessage()
	 */
	public function getMessage() {
		if ($this->fail) return WCF::getLanguage()->get('wcf.chat.suspension.exists');
		
		return serialize(array(
			'link' => $this->link,
			'until' => TIME_NOW + $this->time,
			'type' => str_replace(array('wcf\system\chat\command\commands\\', 'command'), '', strtolower(get_class($this)))
		));
	}
}
