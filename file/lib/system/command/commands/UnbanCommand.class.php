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
class UnbanCommand extends UnmuteCommand {
	/**
	 * @see \chat\system\command\commands\UnmuteCommand::executeAction()
	 */
	public function executeAction() {
		if ($suspension = suspension\Suspension::getSuspensionByUserRoomAndType($this->user, $this->room, suspension\Suspension::TYPE_BAN)) {
			$action = new suspension\SuspensionAction(array($suspension), 'revoke', array(
				'revoker' => WCF::getUser()->userID
			));
			$action->executeAction();
		}
		else {
			throw new \wcf\system\exception\UserInputException('text', WCF::getLanguage()->get('wcf.chat.suspension.notExists'));
		}
	}
}
