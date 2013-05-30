<?php
namespace chat\system\command\commands;
use \chat\data\suspension;
use \chat\util\ChatUtil;
use \wcf\data\user\User;
use \wcf\system\WCF;

/**
 * Unmutes a user globally.
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2013 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	system.chat.command.commands
 */
class GunbanCommand extends UnmuteCommand {
	/**
	 * @see \chat\system\command\commands\UnmuteCommand::executeAction()
	 */
	public function executeAction() {
		$room = new \chat\data\room\Room(null, array('roomID' => null));
		
		if ($suspension = suspension\Suspension::getSuspensionByUserRoomAndType($this->user, $room, suspension\Suspension::TYPE_MUTE)) {
			$action = new suspension\SuspensionAction(array($suspension), 'delete');
			$action->executeAction();
		}
		else {
			throw new \wcf\system\exception\UserInputException('text', WCF::getLanguage()->get('wcf.chat.suspension.notExists'));
		}
	}
}
