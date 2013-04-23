<?php
namespace chat\system\command\commands;
use \chat\data\suspension;
use \chat\util\ChatUtil;
use \wcf\data\user\User;
use \wcf\system\WCF;

/**
 * Globally bans a user.
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2013 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	system.chat.command.commands
 */
class GmuteCommand extends MuteCommand {
	public function executeAction() {
		$room = new \chat\data\room\Room(null, array('roomID' => null));
		
		if ($suspension = suspension\Suspension::getSuspensionByUserRoomAndType($this->user, $room, suspension\Suspension::TYPE_MUTE)) {
			if ($suspension->time > TIME_NOW + $this->time) {
				throw new \wcf\system\exception\UserInputException('text', WCF::getLanguage()->get('wcf.chat.suspension.exists'));
			}
			
			$editor = new suspension\SuspensionEditor($suspension);
			$editor->delete();
		}
		
		$this->suspensionAction = new suspension\SuspensionAction(array(), 'create', array(
			'data' => array(
				'userID' => $this->user->userID,
				'roomID' => null,
				'type' => suspension\Suspension::TYPE_MUTE,
				'time' => TIME_NOW + $this->time
			)
		));
		$this->suspensionAction->executeAction();
	}
}
