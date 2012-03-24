<?php
namespace wcf\system\chat\command;

/**
 * Interface for chat-commands.
 * 
 * @author 	Tim Düsterhus
 * @copyright	2010-2012 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	timwolla.wcf.chat
 * @subpackage	system.chat.command
 */
interface ICommand {
	const SMILEY_OFF = 0;
	const SMILEY_ON = 1;
	const SMILEY_USER = 2;
	
	public function getType();
	public function getMessage();
	public function getReceiver();
}
