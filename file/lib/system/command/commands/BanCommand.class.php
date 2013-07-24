<?php
namespace chat\system\command\commands;

/**
 * Bans a user.
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2013 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	system.chat.command.commands
 */
class BanCommand extends \chat\system\command\AbstractSuspensionCommand {
	const IS_GLOBAL = false;
	const SUSPENSION_TYPE = \chat\data\suspension\Suspension::TYPE_BAN;
}
