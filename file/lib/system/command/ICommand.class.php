<?php
namespace chat\system\command;

/**
 * Interface for chat-commands.
 * 
 * @author 	Tim Düsterhus
 * @copyright	2010-2014 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	system.chat.command
 */
interface ICommand {
	/**
	 * Setting is forced to be disabled.
	 * 
	 * @var integer
	 */
	const SETTING_OFF = 0;
	
	/**
	 * Setting is forced to be enabled.
	 * 
	 * @var integer
	 */
	const SETTING_ON = 1;
	
	/**
	 * The user may decide whether this setting is on or off.
	 * 
	 * @var integer
	 */
	const SETTING_USER = 2;
	
	/**
	 * Returns the message-type for this command.
	 */
	public function getType();
	
	/**
	 * Returns the message-text for this command.
	 */
	public function getMessage();
	
	/**
	 * Returns additionalData to be saved within database
	 */
	public function getAdditionalData();
	
	/**
	 * Returns the receiver for this command.
	 */
	public function getReceiver();
}
