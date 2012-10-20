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
		
		// Username + link to profile
		$color = array(1 => ChatUtil::getRandomNumber(), 2 => ChatUtil::getRandomNumber() * 0xFFFF);
		ChatUtil::writeUserData(array('color' => $color), $this->user);
		
		$profile = \wcf\system\request\LinkHandler::getInstance()->getLink('User', array(
			'object' => $this->user
		));
		$this->link = '<a href="'.$profile.'">'.ChatUtil::gradient($this->user->username, $color[1], $color[2]).'</a>';
		
		$this->didInit();
	}
	
	/**
	 * @see	\wcf\system\chat\command\IRestrictedChatCommand::checkPermission()
	 */
	public function checkPermission() {
		WCF::getSession()->checkPermission('mod.chat.canRestore');
	}
	
	/**
	 * @see	\wcf\system\chat\command\ICommand::getType()
	 */
	public function getType() {
		return \wcf\data\chat\message\ChatMessage::TYPE_INFORMATION;
	}
	
	/**
	 * @see	\wcf\system\chat\command\ICommand::getMessage()
	 */
	public function getMessage() {
		return 'restored '.$this->link;
	}
}
