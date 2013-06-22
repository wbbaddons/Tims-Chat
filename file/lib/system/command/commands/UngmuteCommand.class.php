<?php
namespace chat\system\command\commands;

/**
 * Unmutes a user globally.
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2013 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	system.chat.command.commands
 */
class UngmuteCommand extends \chat\system\command\AbstractUnsuspensionCommand {
	const IDENTIFIER = 'gmute';
	const IS_GLOBAL = true;
	const SUSPENSION_TYPE = \chat\data\suspension\Suspension::TYPE_MUTE;
}
