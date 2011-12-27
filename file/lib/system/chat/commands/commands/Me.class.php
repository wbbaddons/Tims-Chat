<?php
namespace wcf\system\chat\commands\commands;
use \wcf\util\StringUtil;

/**
 * Indicates an action. The message is shown without the colon.
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2011 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	timwolla.wcf.chat
 * @subpackage	system.chat.commands.commands
 */
class Me extends \wcf\system\chat\commands\AbstractCommand {
	const ENABLE_SMILIES = \wcf\system\chat\commands\ICommand::SMILEY_USER;
	
	/**
	 * @see	\wcf\system\chat\commands\ICommand::getType()
	 */
	public function getType() {
		return \wcf\data\chat\message\ChatMessage::TYPE_ME;
	}
	
	/**
	 * @see	\wcf\system\chat\commands\ICommand::getMessage()
	 */
	public function getMessage() {
		return $this->commandHandler->getParameters();
	}
}
