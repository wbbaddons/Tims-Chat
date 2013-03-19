<?php
namespace chat\system\command\commands;

/**
 * Shows the users that are online
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2013 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	system.chat.command.commands
 */
class WhereCommand extends \chat\system\command\AbstractCommand {
	public $enableHTML = self::SETTING_ON;
	
	/**
	 * @see	\chat\system\command\ICommand::getType()
	 */
	public function getType() {
		return \chat\data\message\Message::TYPE_INFORMATION;
	}
	
	/**
	 * @see	\chat\system\command\ICommand::getMessage()
	 */
	public function getMessage() {
		$rooms = \chat\data\room\Room::getCache();
		
		foreach ($rooms as $room) {
			$users = $room->getUsers();
			$tmp = array();
			foreach ($users as $user) {
				$profile = \wcf\system\request\LinkHandler::getInstance()->getLink('User', array(
					'object' => $user
				));
				$tmp[] = "[url='".$profile."']".$user->username.'[/url]';
			}
			if (!empty($tmp)) $lines[] = '[b]'.$room.':[/b] '.implode(', ', $tmp);
		}
		
		return '[list][*]'.implode('[*]', $lines).'[/list]';
	}
	
	/**
	 * @see	\chat\system\command\ICommand::getReceiver()
	 */
	public function getReceiver() {
		return \wcf\system\WCF::getUser()->userID;
	}
}
