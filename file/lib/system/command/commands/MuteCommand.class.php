<?php
namespace chat\system\command\commands;

/**
 * Mutes a user.
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2013 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	system.chat.command.commands
 */
class MuteCommand extends \chat\system\command\AbstractSuspensionCommand {
	const IDENTIFIER = 'mute';
	const IS_GLOBAL = false;
	const SUSPENSION_TYPE = \chat\data\suspension\Suspension::TYPE_MUTE;
}
