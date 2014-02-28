<?php
namespace chat\system\command\commands;
use \wcf\data\user\User;
use \wcf\system\WCF;

/**
 * Invites a user into a temproom.
 * 
 * @author 	Tim Düsterhus
 * @copyright	2010-2014 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	system.chat.command.commands
 */
class InviteCommand extends \chat\system\command\AbstractRestrictedCommand {
	public $user = null;
	public $link = '';
	public $room = null;
	
	public function __construct(\chat\system\command\CommandHandler $commandHandler) {
		parent::__construct($commandHandler);
		
		$username = rtrim($commandHandler->getParameters(), ',');
		$this->user = User::getUserByUsername($username);
		if (!$this->user->userID) throw new \chat\system\command\UserNotFoundException($username);
		
		$acl = \wcf\system\acl\ACLHandler::getInstance();
		$permissions = $acl->getPermissions($acl->getObjectTypeID('be.bastelstu.chat.room'), array($this->room->roomID));
		
		$newPermission = array();
		foreach ($permissions['options'] as $option) {
			$newPermission[$option->optionID] = ($option->categoryName == 'user') ? 1 : 0;
		}
		
		$_POST['aclValues'] = array(
			'user' => $permissions['user'][$this->room->roomID],
			'group' => $permissions['group'][$this->room->roomID]
		);
		$_POST['aclValues']['user'][$this->user->userID] = $newPermission;
		
		$acl->save($this->room->roomID, $acl->getObjectTypeID('be.bastelstu.chat.room'));
		\chat\system\permission\PermissionHandler::clearCache();
		$this->didInit();
	}
	
	/**
	 * @see	\chat\system\command\IRestrictedChatCommand::checkPermission()
	 */
	public function checkPermission() {
		parent::checkPermission();
		
		$this->room = $this->commandHandler->getRoom();
		if ($this->room->owner != WCF::getUser()->userID) throw new \wcf\system\exception\PermissionDeniedException();
	}
	
	/**
	 * @see	\chat\system\command\ICommand::getType()
	 */
	public function getType() {
		return \chat\data\message\Message::TYPE_INFORMATION;
	}
	
	/**
	 * @see	\chat\system\command\ICommand::getMessage()
	 */
	public function getMessage() {
		return \wcf\system\WCF::getLanguage()->getDynamicVariable('chat.message.invite.success', array('user' => $this->user, 'room' => $this->room));
	}
}
