<?php
namespace chat\system\command\commands;
use \chat\data\suspension;
use \chat\util\ChatUtil;
use \wcf\data\user\User;
use \wcf\system\WCF;

/**
 * Mutes a user.
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2013 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	system.chat.command.commands
 */
class MuteCommand extends \chat\system\command\AbstractRestrictedCommand {
	const SUSPENSION_TYPE = suspension\Suspension::TYPE_MUTE;
	public $user = null;
	public $expires = 0;
	public $suspensionAction = null;
	public $link = '';
	public $room = null;
	public $reason = '';
	
	public function __construct(\chat\system\command\CommandHandler $commandHandler) {
		parent::__construct($commandHandler);
		
		try {
			$parameters = explode(',', $commandHandler->getParameters(), 3);
			list($username, $modifier) = $parameters;
			
			if (isset($parameters[2])) {
				$this->reason = \wcf\util\StringUtil::trim($parameters[2]);
			}
			
			$modifier = ChatUtil::timeModifier(\wcf\util\StringUtil::trim($modifier));
			$expires = strtotime($modifier, TIME_NOW);
			$this->expires = min(max(-0x80000000, $expires), 0x7FFFFFFF);
		}
		catch (\wcf\system\exception\SystemException $e) {
			throw new \chat\system\command\NotFoundException();
		}
		
		$this->user = User::getUserByUsername($username);
		if (!$this->user->userID) throw new \chat\system\command\UserNotFoundException($username);
		
		$profile = \wcf\system\request\LinkHandler::getInstance()->getLink('User', array(
			'object' => $this->user
		));
		$this->link = "[url='".$profile."']".$this->user->username.'[/url]';
		
		$this->executeAction();
		
		$this->didInit();
	}
	
	public function executeAction() {
		if ($suspension = suspension\Suspension::getSuspensionByUserRoomAndType($this->user, $this->room, static::SUSPENSION_TYPE)) {
			if ($suspension->expires >= $this->expires) {
				throw new \wcf\system\exception\UserInputException('text', WCF::getLanguage()->get('wcf.chat.suspension.exists'));
			}
			
			$action = new suspension\SuspensionAction(array($suspension), 'delete');
			$action->executeAction();
		}
		
		$this->suspensionAction = new suspension\SuspensionAction(array(), 'create', array(
			'data' => array(
				'userID' => $this->user->userID,
				'roomID' => WCF::getUser()->chatRoomID,
				'type' => suspension\Suspension::TYPE_MUTE,
				'expires' => $this->expires,
				'time' => TIME_NOW,
				'issuer' => WCF::getUser()->userID,
				'reason' => $this->reason
			)
		));
		$this->suspensionAction->executeAction();
	}
	
	/**
	 * @see	\chat\system\command\IRestrictedChatCommand::checkPermission()
	 */
	public function checkPermission() {
		parent::checkPermission();
		
		$this->room = $this->commandHandler->getRoom();
		$ph = new \chat\system\permission\PermissionHandler();
		if (!$ph->getPermission($this->room, 'mod.can'.str_replace(array('chat\system\command\commands\\', 'Command'), '', get_class($this)))) throw new \wcf\system\exception\PermissionDeniedException();
	}
	
	/**
	 * @see	\chat\system\command\ICommand::getType()
	 */
	public function getType() {
		return \chat\data\message\Message::TYPE_MODERATE;
	}
	
	/**
	 * @see	\chat\system\command\ICommand::getMessage()
	 */
	public function getMessage() {
		return serialize(array(
			'link' => $this->link,
			'expires' => $this->expires,
			'type' => str_replace(array('chat\system\command\commands\\', 'command'), '', strtolower(get_class($this))),
			'message' => $this->suspensionMessage
		));
	}
}
