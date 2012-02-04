<?php
namespace wcf\system\chat\commands\commands;

/**
 * Sends a message that starts with a slash.
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2012 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	timwolla.wcf.chat
 * @subpackage	system.chat.commands.commands
 */
class Plain extends \wcf\system\chat\commands\AbstractCommand {
	public $enableSmilies = \wcf\system\chat\commands\ICommand::SMILEY_USER;
	
	/**
	 * @see	\wcf\system\chat\commands\ICommand::getType()
	 */
	public function getType() {
		return \wcf\data\chat\message\ChatMessage::TYPE_NORMAL;
	}
	
	/**
	 * @see	\wcf\system\chat\commands\ICommand::getMessage()
	 */
	public function getMessage() {
		return \wcf\util\StringUtil::substring($this->commandHandler->getText(), 1);
	}
	
	/**
	 * @see	\wcf\system\chat\commands\ICommand::getReceiver()
	 */
	public function getReceiver() {
		return null;
	}
}
