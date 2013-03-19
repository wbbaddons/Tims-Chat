<?php
namespace chat\system\command\commands;

/**
 * Sends a message that starts with a slash.
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2013 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	system.chat.command.commands
 */
class PlainCommand extends \chat\system\command\AbstractCommand {
	public $enableSmilies = \chat\system\command\ICommand::SETTING_USER;
	
	/**
	 * @see	\chat\system\command\ICommand::getType()
	 */
	public function getType() {
		return \chat\data\message\Message::TYPE_NORMAL;
	}
	
	/**
	 * @see	\chat\system\command\ICommand::getMessage()
	 */
	public function getMessage() {
		return \wcf\system\bbcode\PreParser::getInstance()->parse(\wcf\util\StringUtil::substring($this->commandHandler->getText(), 1), explode(',', \wcf\system\WCF::getSession()->getPermission('user.chat.allowedBBCodes')));
	}
	
	/**
	 * @see	\chat\system\command\ICommand::getReceiver()
	 */
	public function getReceiver() {
		return null;
	}
}
