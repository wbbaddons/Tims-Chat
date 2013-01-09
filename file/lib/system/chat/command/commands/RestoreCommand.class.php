<?php
namespace wcf\system\chat\command\commands;
use \wcf\data\user\User;
use \wcf\system\WCF;
use \wcf\util\ChatUtil;

/**
 * Resets the color of a user
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2012 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.wcf.chat
 * @subpackage	system.chat.command.commands
 */
class RestoreCommand extends \wcf\system\chat\command\AbstractRestrictedCommand {
	public $enableHTML = 1;
	public $user = null;
	public $link = '';
	
	public function __construct(\wcf\system\chat\command\CommandHandler $commandHandler) {
		parent::__construct($commandHandler);
		
		$this->user = User::getUserByUsername(rtrim($commandHandler->getParameters(), ','));
		if (!$this->user->userID) throw new \wcf\system\chat\command\UserNotFoundException(rtrim($commandHandler->getParameters(), ','));
		
		$this->link = '<span class="userLink" data-user-id="'.$this->user->userID.'" />';
		
		$this->didInit();
	}
	
	/**
	 * @see	\wcf\system\chat\command\IRestrictedChatCommand::checkPermission()
	 */
	public function checkPermission() {
		parent::checkPermission();
		
		WCF::getSession()->checkPermissions(array('mod.chat.canRestore'));
	}
	
	/**
	 * @see	\wcf\system\chat\command\ICommand::getType()
	 */
	public function getType() {
		return \wcf\data\chat\message\ChatMessage::TYPE_MODERATE;
	}
	
	/**
	 * @see	\wcf\system\chat\command\ICommand::getMessage()
	 */
	public function getMessage() {
		return serialize(array(
			'link' => $this->link,
			'type' => str_replace(array('wcf\system\chat\command\commands\\', 'command'), '', strtolower(get_class($this)))
		));
	}
}
