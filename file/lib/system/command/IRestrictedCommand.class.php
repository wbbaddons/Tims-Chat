<?php
namespace chat\system\command;

/**
 * Interface for Restricted commands.
 * 
 * @author 	Tim Düsterhus
 * @copyright	2010-2013 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	system.chat.command
 */
interface IRestrictedCommand {
	/** 
	 * Checks the permission to use this command. Has to throw
	 * \wcf\system\exception\PermissionDeniedException when the 
	 * user is not allowed to use the command.
	 */
	public function checkPermission();
}
