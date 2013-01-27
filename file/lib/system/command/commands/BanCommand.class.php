<?php
namespace chat\system\command\commands;
use \chat\data\suspension;
use \chat\util\ChatUtil;
use \wcf\data\user\User;
use \wcf\system\WCF;

/**
 * Bans a user.
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2013 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	system.chat.command.commands
 */
class BanCommand extends MuteCommand {
	public function executeAction() {
		if ($suspension = suspension\Suspension::getSuspensionByUserRoomAndType($this->user, $this->room, suspension\Suspension::TYPE_BAN)) {
			if ($suspension->time > TIME_NOW + $this->time) {
				$this->fail = true;
				return;
			}
			
			$editor = new suspension\SuspensionEditor($suspension);
			$editor->delete();
		}
		
		$this->suspensionAction = new suspension\SuspensionAction(array(), 'create', array(
			'data' => array(
				'userID' => $this->user->userID,
				'roomID' => ChatUtil::readUserData('roomID'),
				'type' => suspension\Suspension::TYPE_BAN,
				'time' => TIME_NOW + $this->time
			)
		));
		$this->suspensionAction->executeAction();
	}
}
