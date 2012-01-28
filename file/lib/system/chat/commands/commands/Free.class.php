<?php
namespace wcf\system\chat\commands\commands;

/**
 * Informs everyone that the fish was freed. OH A NOEZ.
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2011 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	timwolla.wcf.chat
 * @subpackage	system.chat.commands.commands
 */
class Free extends Me {
	const ENABLE_SMILIES = \wcf\system\chat\commands\ICommand::SMILEY_OFF;
	
	/**
	 * @see	\wcf\system\chat\commands\ICommand::getMessage()
	 */
	public function getMessage() {
		if (\wcf\util\StringUtil::toLowerCase($this->commandHandler->getParameters()) == 'the fish')
			return 'freed the fish. OH A NOEZ';
		else
			throw new \wcf\system\chat\commands\NotFoundException();
	}
}
