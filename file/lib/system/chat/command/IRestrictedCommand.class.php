<?php
namespace wcf\system\chat\command;

/**
 * Interface for Restricted commands.
 * 
 * @author 	Tim Düsterhus
 * @copyright	2010-2012 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.wcf.chat
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
