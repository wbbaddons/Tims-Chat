<?php
namespace chat\system\command\commands;
use \chat\data\suspension;
use \wcf\data\user\User;
use \wcf\system\WCF;
use \chat\util\ChatUtil;

/**
 * Mutes a user.
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2013 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	system.chat.command.commands
 */
class MuteCommand extends \chat\system\command\AbstractRestrictedCommand {
	public $user = null;
	public $time = 0;
	public $suspensionAction = null;
	public $link = '';
	public $fail = false;
	public $room = null;
	
	public function __construct(\chat\system\command\CommandHandler $commandHandler) {
		parent::__construct($commandHandler);
		
		$parameters = $commandHandler->getParameters();
		if (($comma = strpos($parameters, ',')) !== false) {
			$username = substr($parameters, 0, $comma);
			$this->time = ChatUtil::timeModifier(substr($parameters, $comma + 1));
		}
		else {
			throw new \chat\system\command\NotFoundException();
		}
		
		$this->user = User::getUserByUsername($username);
		if (!$this->user->userID) throw new \chat\system\command\UserNotFoundException($username);
		
		$color = ChatUtil::readUserData('color', $this->user);
		$profile = \wcf\system\request\LinkHandler::getInstance()->getLink('User', array(
			'object' => $this->user
		));
		$this->link = '<span class="userLink" data-user-id="'.$this->user->userID.'" />';
		
		$this->executeAction();
		
		$this->didInit();
	}
	
	public function executeAction() {
		if ($suspension = suspension\Suspension::getSuspensionByUserRoomAndType($this->user, $this->room, suspension\Suspension::TYPE_MUTE)) {
			if ($suspension->time > TIME_NOW + $this->time) {
				$this->fail = true;
				return;
			}
			
			$editor = new suspension\SuspensionEditor($suspension);
			$editor->delete();
		}
		
		$this->suspensionAction = new suspension\SuspensionAction(array(), 'create', array(
			'data' => array(
				'userID' => $this->user->userID,
				'roomID' => ChatUtil::readUserData('roomID'),
				'type' => suspension\Suspension::TYPE_MUTE,
				'time' => TIME_NOW + $this->time
			)
		));
		$this->suspensionAction->executeAction();
	}
	
	/**
	 * @see	\chat\system\command\IRestrictedChatCommand::checkPermission()
	 */
	public function checkPermission() {
		parent::checkPermission();
		
		$this->room = $this->commandHandler->getRoom();
		$ph = new \chat\system\permission\PermissionHandler();
		if (!$ph->getPermission($this->room, 'mod.can'.str_replace(array('chat\system\command\commands\\', 'Command'), '', get_class($this)))) throw new \wcf\system\exception\PermissionDeniedException();
	}
	
	/**
	 * @see	chat\system\command\ICommand::getReceiver()
	 */
	public function getReceiver() {
		if ($this->fail) return WCF::getUser()->userID;
		
		return parent::getReceiver();
	}
	
	/**
	 * @see	\chat\system\command\ICommand::getType()
	 */
	public function getType() {
		if ($this->fail) return \chat\data\message\Message::TYPE_INFORMATION;
		return \chat\data\message\Message::TYPE_MODERATE;
	}
	
	/**
	 * @see	\chat\system\command\ICommand::getMessage()
	 */
	public function getMessage() {
		if ($this->fail) return WCF::getLanguage()->get('wcf.chat.suspension.exists');
		
		return serialize(array(
			'link' => $this->link,
			'until' => TIME_NOW + $this->time,
			'type' => str_replace(array('chat\system\command\commands\\', 'command'), '', strtolower(get_class($this)))
		));
	}
}
