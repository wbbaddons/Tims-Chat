<?php
namespace chat\system\command\commands;
use \wcf\data\user\User;
use \wcf\system\WCF;
use \wcf\util\ChatUtil;

/**
 * Resets the color of a user
 * 
 * @author 	Tim Düsterhus
 * @copyright	2010-2014 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	system.chat.command.commands
 */
class RestoreCommand extends \chat\system\command\AbstractRestrictedCommand {
	public $enableHTML = 1;
	public $user = null;
	public $link = '';
	
	public function __construct(\chat\system\command\CommandHandler $commandHandler) {
		parent::__construct($commandHandler);
		
		$username = rtrim($commandHandler->getParameters(), ',');
		$this->user = User::getUserByUsername($username);
		if (!$this->user->userID) throw new \chat\system\command\UserNotFoundException($username);
		
		$profile = \wcf\system\request\LinkHandler::getInstance()->getLink('User', array(
			'object' => $this->user
		));
		$this->link = "[url='".$profile."']".$this->user->username.'[/url]';
		
		$editor = new \wcf\data\user\UserEditor($this->user);
		$editor->update(array(
			'chatColor1' => null,
			'chatColor2' => null
		));
		$this->didInit();
	}
	
	/**
	 * @see	\chat\system\command\IRestrictedChatCommand::checkPermission()
	 */
	public function checkPermission() {
		parent::checkPermission();
		
		WCF::getSession()->checkPermissions(array('mod.chat.canRestore'));
	}
	
	/**
	 * @see	\chat\system\command\ICommand::getType()
	 */
	public function getType() {
		return \chat\data\message\Message::TYPE_MODERATE;
	}
	
	/**
	 * @see	\chat\system\command\ICommand::getMessage()
	 */
	public function getMessage() {
		return serialize(array(
			'link' => $this->link,
			'type' => str_replace(array('chat\system\command\commands\\', 'command'), '', strtolower(get_class($this)))
		));
	}
}
