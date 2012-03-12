<?php
namespace wcf\system\chat\commands;
use \wcf\util\StringUtil;

/**
 * Inserts a message.
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2012 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.wcf.chat
 * @subpackage	system.chat.commands
 */
class ChatCommandHandler {
	const COMMAND_CHAR = '/';
	
	/**
	 * Checks whether the given text is a command.
	 * 
	 * @param	string	$text
	 * @return	boolean
	 */
	public function isCommand($text) {
		return StringUtil::substring($text, 0, StringUtil::length(static::COMMAND_CHAR)) == static::COMMAND_CHAR;
	}
}
