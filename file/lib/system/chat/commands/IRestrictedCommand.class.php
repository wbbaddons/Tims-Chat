<?php
namespace wcf\system\chat\commands;

/**
 * Interface for Restricted commands.
 * 
 * @author 	Tim Düsterhus
 * @copyright	2010-2011 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	timwolla.wcf.chat
 * @subpackage	system.chat.commands
 */
interface IRestrictedCommand {
	protected function checkPermission();
}
