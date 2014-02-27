<?php
namespace chat\system\command\commands;

/**
 * Bans a user.
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2014 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	system.chat.command.commands
 */
class UnbanCommand extends \chat\system\command\AbstractUnsuspensionCommand {
	const IS_GLOBAL = false;
	const SUSPENSION_TYPE = \chat\data\suspension\Suspension::TYPE_BAN;
}
