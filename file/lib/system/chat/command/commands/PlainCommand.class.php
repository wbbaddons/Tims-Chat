<?php
namespace wcf\system\chat\command\commands;

/**
 * Sends a message that starts with a slash.
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2012 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.wcf.chat
 * @subpackage	system.chat.command.commands
 */
class PlainCommand extends \wcf\system\chat\command\AbstractCommand {
	public $enableSmilies = \wcf\system\chat\command\ICommand::SMILEY_USER;
	
	/**
	 * @see	\wcf\system\chat\command\ICommand::getType()
	 */
	public function getType() {
		return \wcf\data\chat\message\ChatMessage::TYPE_NORMAL;
	}
	
	/**
	 * @see	\wcf\system\chat\command\ICommand::getMessage()
	 */
	public function getMessage() {
		return \wcf\util\StringUtil::substring($this->commandHandler->getText(), 1);
	}
	
	/**
	 * @see	\wcf\system\chat\command\ICommand::getReceiver()
	 */
	public function getReceiver() {
		return null;
	}
}
