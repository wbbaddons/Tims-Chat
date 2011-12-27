<?php
namespace wcf\system\chat\commands\commands;

/**
 * Free-Command
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2011 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	timwolla.wcf.chat
 * @subpackage	system.chat.commands.commands
 */
class Free extends \wcf\system\chat\commands\AbstractCommand {
	const ENABLE_SMILIES = \wcf\system\chat\commands\ICommand::SMILEY_USER;
	
	public function getType() {
		return \wcf\data\chat\message\ChatMessage::TYPE_ME;
	}
	
	public function getMessage() {
		return 'freed the fish';
	}
}
