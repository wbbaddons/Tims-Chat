<?php
namespace wcf\system\chat\command\commands;
use \wcf\data\user\User;
use \wcf\system\WCF;
use \wcf\util\ChatUtil;
use \wcf\util\StringUtil;

/**
 * Shows information about the specified user.
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2012 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	timwolla.wcf.chat
 * @subpackage	system.chat.command.commands
 */
class Info extends \wcf\system\chat\command\AbstractCommand {
	public $enableSmilies = \wcf\system\chat\command\ICommand::SMILEY_OFF;
	public $enableHTML = 1;
	private $lines = array();
	
	public function __construct(\wcf\system\chat\command\CommandHandler $commandHandler) {
		parent::__construct($commandHandler);
		
		$user = User::getUserByUsername(rtrim($commandHandler->getParameters(), ','));
		if (!$user->userID) throw new \wcf\system\chat\command\UserNotFoundException(rtrim($commandHandler->getParameters(), ','));
		$room = new \wcf\data\chat\room\ChatRoom(ChatUtil::readUserData('roomID', $user));
		$color = ChatUtil::readUserData('color', $user);
		
		$this->lines[WCF::getLanguage()->get('wcf.user.username')] = ChatUtil::gradient($user->username, $color[1], $color[2]);
		if (ChatUtil::readUserData('away', $user) !== null) {
			$this->lines[WCF::getLanguage()->get('wcf.chat.away')] = ChatUtil::readUserData('away', $user);
		}
		if ($room->roomID && $room->canEnter()) {
			$this->lines[WCF::getLanguage()->get('wcf.chat.room')] = $room->getTitle();
		}
		$session = $this->fetchSession($user);
		if ($session) {
			// TODO: Check permission
			$this->lines['IP_ADDRESS'] = $session->ipAddress;
		}
		
		$this->didInit();
	}
	
	public function fetchSession(User $user) {
		$sql = "SELECT
				*
			FROM
				wcf".WCF_N."_session
			WHERE
				userID = ?";
		$stmt = WCF::getDB()->prepareStatement($sql);
		$stmt->execute(array($user->userID));
		$row = $stmt->fetchArray();
		if (!$row) return false;
		
		return new \wcf\data\session\Session(null, $row);
	}
	
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
		$lines = array();
		foreach ($this->lines as $key => $val) {
			$lines[] = '<strong>'.$key.':</strong> '.$val;
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
