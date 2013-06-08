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
			if ($suspension->expires > $this->expires) {
				throw new \wcf\system\exception\UserInputException('text', WCF::getLanguage()->get('wcf.chat.suspension.exists'));
			}
			
			$action = new suspension\SuspensionAction(array($suspension), 'delete');
			$action->executeAction();
		}
		
		$this->suspensionAction = new suspension\SuspensionAction(array(), 'create', array(
			'data' => array(
				'userID' => $this->user->userID,
				'roomID' => null,
				'type' => suspension\Suspension::TYPE_MUTE,
				'expires' => $this->expires,
				'time' => TIME_NOW,
				'issuer' => WCF::getUser()->userID
			)
		));
		$this->suspensionAction->executeAction();
	}
}
