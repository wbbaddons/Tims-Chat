<?php
namespace wcf\system\chat\command\commands;

/**
 * Shows the users that are online
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2012 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	timwolla.wcf.chat
 * @subpackage	system.chat.command.commands
 */
class Where extends \wcf\system\chat\command\AbstractCommand {
	public $enableSmilies = \wcf\system\chat\command\ICommand::SMILEY_OFF;
	public $enableHTML = 1;
	
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
				$profile = \wcf\system\request\LinkHandler::getInstance()->getLink('User', array(
					'object' => $user
				));
				
				$tmp[] = '<a href="'.$profile.'">'.$user.'</a>';
			}
			if (!empty($tmp)) $lines[] = '<strong>'.$room.':</strong> '.implode(', ', $tmp);
		}
		
		return '<ul><li>'.implode('</li><li>', $lines).'</li></ul>';
	}
	
	/**
	 * @see	\wcf\system\chat\command\ICommand::getReceiver()
	 */
	public function getReceiver() {
		return \wcf\system\WCF::getUser()->userID;
	}
}
