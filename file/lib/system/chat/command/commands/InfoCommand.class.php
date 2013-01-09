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
 * @package	be.bastelstu.wcf.chat
 * @subpackage	system.chat.command.commands
 */
class InfoCommand extends \wcf\system\chat\command\AbstractCommand {
	public $enableBBCodes = self::SETTING_ON;
	public $enableHTML = self::SETTING_ON;	
	public $lines = array();
	public $user = null;
	
	public function __construct(\wcf\system\chat\command\CommandHandler $commandHandler) {
		parent::__construct($commandHandler);
		
		$this->user = User::getUserByUsername(rtrim($commandHandler->getParameters(), ','));
		if (!$this->user->userID) throw new \wcf\system\chat\command\UserNotFoundException(rtrim($commandHandler->getParameters(), ','));
		
		// Username + link to profile
		$color = ChatUtil::readUserData('color', $this->user);
		$profile = \wcf\system\request\LinkHandler::getInstance()->getLink('User', array(
				'object' => $this->user
		));
		$this->lines[WCF::getLanguage()->get('wcf.user.username')] = '<span class="userLink" data-user-id="'.$this->user->userID.'" />';
		
		// Away-Status
		if (ChatUtil::readUserData('away', $this->user) !== null) {
			$this->lines[WCF::getLanguage()->get('wcf.chat.away')] = ChatUtil::readUserData('away', $this->user);
		}
		
		// Room
		$room = new \wcf\data\chat\room\ChatRoom(ChatUtil::readUserData('roomID', $this->user));
		if ($room->roomID && $room->canEnter()) {
			$this->lines[WCF::getLanguage()->get('wcf.chat.room')] = $room->getTitle();
		}
		
		// IP-Address
		$session = $this->fetchSession();
		if ($session) {
			// TODO: Check permission
			$this->lines[WCF::getLanguage()->get('wcf.user.ipAddress')] = $session->ipAddress;
		}
		
		$this->didInit();
	}
	
	/**
	 * Fetches the sessiondatabase object for the specified user.
	 * 
	 * @return	\wcf\data\session\Session
	 */
	public function fetchSession() {
		$sql = "SELECT
				*
			FROM
				wcf".WCF_N."_session
			WHERE
				userID = ?";
		$stmt = WCF::getDB()->prepareStatement($sql);
		$stmt->execute(array($this->user->userID));
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
			$lines[] = '[b]'.$key.':[/b] '.$val;
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
