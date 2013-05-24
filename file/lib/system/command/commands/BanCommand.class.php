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
			if ($suspension->expires > $this->expires) {
				throw new \wcf\system\exception\UserInputException('text', WCF::getLanguage()->get('wcf.chat.suspension.exists'));
			}
			
			$action = new suspension\SuspensionAction(array($suspension), 'delete');
			$action->executeAction();
		}
		
		$this->suspensionAction = new suspension\SuspensionAction(array(), 'create', array(
			'data' => array(
				'userID' => $this->user->userID,
				'roomID' => WCF::getUser()->chatRoomID,
				'type' => suspension\Suspension::TYPE_BAN,
				'expires' => $this->expires
			)
		));
		$this->suspensionAction->executeAction();
	}
}
