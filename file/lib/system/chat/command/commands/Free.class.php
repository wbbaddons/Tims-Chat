<?php
namespace wcf\system\chat\command\commands;

/**
 * Informs everyone that the fish was freed. OH A NOEZ.
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2012 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	timwolla.wcf.chat
 * @subpackage	system.chat.command.commands
 */
class Free extends Me {
	public function __construct(\wcf\system\chat\command\CommandHandler $commandHandler) {
		parent::__construct($commandHandler);
		
		if (\wcf\util\StringUtil::toLowerCase($this->commandHandler->getParameters()) != 'the fish') {
			throw new \wcf\system\chat\command\NotFoundException();
		}
		
		$this->didInit();
	}
	
	/**
	 * @see	\wcf\system\chat\command\ICommand::getMessage()
	 */
	public function getMessage() {
		return 'freed the fish. OH A NOEZ';
	}
}
