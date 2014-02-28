<?php
namespace chat\system\command\commands;

/**
 * Unbans a user globally.
 * 
 * @author 	Tim Düsterhus
 * @copyright	2010-2014 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	system.chat.command.commands
 */
class UngbanCommand extends \chat\system\command\AbstractUnsuspensionCommand {
	const IS_GLOBAL = true;
	const SUSPENSION_TYPE = \chat\data\suspension\Suspension::TYPE_BAN;
}
