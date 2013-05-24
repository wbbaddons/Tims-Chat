<?php
namespace chat\system\command\commands;
use \chat\util\ChatUtil;
use \wcf\data\user\User;
use \wcf\system\WCF;
use \wcf\util\DateUtil;
use \wcf\util\StringUtil;

/**
 * Shows information about the specified user.
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2013 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	system.chat.command.commands
 */
class InfoCommand extends \chat\system\command\AbstractCommand {
	public $enableHTML = self::SETTING_ON;	
	public $lines = array();
	public $user = null;
	
	public function __construct(\chat\system\command\CommandHandler $commandHandler) {
		parent::__construct($commandHandler);
		
		$username = rtrim($commandHandler->getParameters(), ',');
		$this->user = User::getUserByUsername($username);
		if (!$this->user->userID) throw new \chat\system\command\UserNotFoundException($username);
		
		// Username + link to profile
		$profile = \wcf\system\request\LinkHandler::getInstance()->getLink('User', array(
			'object' => $this->user
		));
		$this->lines[WCF::getLanguage()->get('wcf.user.username')] = "[url='".$profile."']".$this->user->username.'[/url]';
		
		// Away-Status
		if ($this->user->chatAway !== null) {
			$this->lines[WCF::getLanguage()->get('wcf.chat.away')] = $this->user->chatAway;
		}
		
		// Room
		$room = \chat\data\room\RoomCache::getInstance()->getRoom($this->user->chatRoomID);
		if ($room !== null && $room->canEnter()) {
			$this->lines[WCF::getLanguage()->get('chat.general.room')] = $room->getTitle();
		}
		
		// Suspensions
		// TODO: Permissions
		$suspensions = \chat\data\suspension\Suspension::getSuspensionsForUser($this->user);
		foreach ($suspensions as $roomSuspensions) {
			foreach ($roomSuspensions as $typeSuspension) {
				if (!$typeSuspension->isValid()) continue;
				
				$dateTime = DateUtil::getDateTimeByTimestamp($typeSuspension->expires);
				$name = WCF::getLanguage()->getDynamicVariable('chat.information.suspension', array(
					'suspension' => $typeSuspension,
					'room' => \chat\data\room\RoomCache::getInstance()->getRoom($typeSuspension->roomID)
				));
				$this->lines[$name] = str_replace('%time%', DateUtil::format($dateTime, DateUtil::TIME_FORMAT), str_replace('%date%', DateUtil::format($dateTime, DateUtil::DATE_FORMAT), WCF::getLanguage()->get('wcf.date.dateTimeFormat')));
			}
		}
		
		// ip address
		if (WCF::getSession()->getPermission('admin.user.canViewIpAddress')) {
			$session = $this->fetchSession();
			if ($session) {
				$this->lines[WCF::getLanguage()->get('wcf.user.ipAddress')] = \wcf\util\UserUtil::convertIPv6To4($session->ipAddress);
			}
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
	 * @see	\chat\system\command\ICommand::getType()
	 */
	public function getType() {
		return \chat\data\message\Message::TYPE_INFORMATION;
	}
	
	/**
	 * @see	\chat\system\command\ICommand::getMessage()
	 */
	public function getMessage() {
		$lines = array();
		foreach ($this->lines as $key => $val) {
			$lines[] = '[b]'.$key.':[/b] '.$val;
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
