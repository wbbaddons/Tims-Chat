<?php
namespace wcf\system\chat\command\commands;

/**
 * Shows the users that are online
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2012 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.wcf.chat
 * @subpackage	system.chat.command.commands
 */
class WhereCommand extends \wcf\system\chat\command\AbstractCommand {
	public $enableHTML = self::SETTING_ON;
	public $enableBBCodes = self::SETTING_ON;
	
	/**
	 * @see	\wcf\system\chat\command\ICommand::getType()
	 */
	public function getType() {
		return \wcf\data\chat\message\ChatMessage::TYPE_INFORMATION;
	}
	
	/**
	 * @see	\wcf\system\chat\command\ICommand::getMessage()
	 */
	public function getMessage() {
		$rooms = \wcf\data\chat\room\ChatRoom::getCache();
		
		foreach ($rooms as $room) {
			$users = $room->getUsers();
			$tmp = array();
			foreach ($users as $user) {
				$tmp[] = '<span class="userLink" data-user-id="'.$user->userID.'" />';
			}
			if (!empty($tmp)) $lines[] = '[b]'.$room.':[/b] '.implode(', ', $tmp);
		}
		
		return '[list][*]'.implode('[*]', $lines).'[/list]';
	}
	
	/**
	 * @see	\wcf\system\chat\command\ICommand::getReceiver()
	 */
	public function getReceiver() {
		return \wcf\system\WCF::getUser()->userID;
	}
}
