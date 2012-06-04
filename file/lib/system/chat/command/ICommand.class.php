<?php
namespace wcf\system\chat\command;

/**
 * Interface for chat-commands.
 * 
 * @author 	Tim Düsterhus
 * @copyright	2010-2012 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.wcf.chat
 * @subpackage	system.chat.command
 */
interface ICommand {
	/**
	 * Smilies are forced to be disabled.
	 * 
	 * @var integer
	 */
	const SMILEY_OFF = 0;
	
	/**
	 * Smilies are forced to be enabled.
	 * 
	 * @var integer
	 */
	const SMILEY_ON = 1;
	
	/**
	 * The user may decide whether smilies are on or off.
	 * 
	 * @var integer
	 */
	const SMILEY_USER = 2;
	
	/**
	 * Returns the message-type for this command.
	 */
	public function getType();
	
	/**
	 * Returns the message-text for this command.
	 */
	public function getMessage();
	
	/**
	 * Returns the receiver for this command.
	 */
	public function getReceiver();
}
