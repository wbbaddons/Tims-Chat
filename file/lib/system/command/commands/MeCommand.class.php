<?php
namespace chat\system\command\commands;

/**
 * Indicates an action. The message is shown without the colon.
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2013 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	system.chat.command.commands
 */
class MeCommand extends \chat\system\command\AbstractCommand {
	/**
	 * @see	\chat\system\command\AbstractCommand::$enableSmilies
	 */
	public $enableSmilies = self::SETTING_USER;
	
	public function __construct(\chat\system\command\CommandHandler $commandHandler) {
		parent::__construct($commandHandler);
		
		$this->didInit();
	}
	
	/**
	 * @see	\chat\system\command\ICommand::getType()
	 */
	public function getType() {
		return \chat\data\message\Message::TYPE_ME;
	}
	
	/**
	 * @see	\chat\system\command\ICommand::getMessage()
	 */
	public function getMessage() {
		return $this->commandHandler->getParameters();
	}
}
