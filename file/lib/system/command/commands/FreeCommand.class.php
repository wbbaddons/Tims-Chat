<?php
namespace chat\system\command\commands;

/**
 * Informs everyone that the fish was freed. OH A NOEZ.
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2013 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	system.chat.command.commands
 */
class FreeCommand extends MeCommand {
	public function __construct(\chat\system\command\CommandHandler $commandHandler) {
		parent::__construct($commandHandler);
		
		if (\wcf\util\StringUtil::toLowerCase($this->commandHandler->getParameters()) != 'the fish') {
			throw new \InvalidArgumentException();
		}
		
		$this->didInit();
	}
	
	/**
	 * @see	\chat\system\command\ICommand::getMessage()
	 */
	public function getMessage() {
		return 'freed the fish. OH A NOEZ';
	}
}
