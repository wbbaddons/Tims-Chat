<?php
namespace chat\system\command\commands;
use \chat\data\suspension;

/**
 * Globally bans a user.
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2013 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	system.chat.command.commands
 */
class GbanCommand extends GmuteCommand {
	const SUSPENSION_TYPE = suspension\Suspension::TYPE_BAN;
}
