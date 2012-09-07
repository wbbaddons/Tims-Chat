<?php
namespace wcf\system\chat\command\commands;
use \wcf\util\StringUtil;

/**
 * Indicates an action. The message is shown without the colon.
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2012 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.wcf.chat
 * @subpackage	system.chat.command.commands
 */
class MeCommand extends \wcf\system\chat\command\AbstractCommand {
	public $enableSmilies = \wcf\system\chat\command\ICommand::SMILEY_USER;
	
	public function __construct(\wcf\system\chat\command\CommandHandler $commandHandler) {
		parent::__construct($commandHandler);
		
		$this->didInit();
	}
	
	/**
	 * @see	\wcf\system\chat\command\ICommand::getType()
	 */
	public function getType() {
		return \wcf\data\chat\message\ChatMessage::TYPE_ME;
	}
	
	/**
	 * @see	\wcf\system\chat\command\ICommand::getMessage()
	 */
	public function getMessage() {
		return $this->commandHandler->getParameters();
	}
}
