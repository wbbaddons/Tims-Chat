<?php
namespace chat\system\command\commands;
use \wcf\system\WCF;

/**
 * Marks the user as away.
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2013 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	system.chat.command.commands
 */
class AwayCommand extends \chat\system\command\AbstractCommand {
	public function __construct(\chat\system\command\CommandHandler $commandHandler) {
		parent::__construct($commandHandler);
		
		$editor = new \wcf\data\user\UserEditor(WCF::getUser());
		$editor->update(array(
			'chatAway' => $commandHandler->getParameters()
		));
		$this->didInit();
	}
	
	/**
	 * @see	\chat\system\command\ICommand::getType()
	 */
	public function getType() {
		return \chat\data\message\Message::TYPE_AWAY;
	}
	
	/**
	 * @see	\chat\system\command\ICommand::getMessage()
	 */
	public function getMessage() {
		return \wcf\system\bbcode\PreParser::getInstance()->parse($this->commandHandler->getParameters(), explode(',', WCF::getSession()->getPermission('user.chat.allowedBBCodes')));
	}
}
