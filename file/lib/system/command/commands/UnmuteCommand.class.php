<?php
namespace chat\system\command\commands;
use \chat\data\suspension;
use \wcf\data\user\User;
use \wcf\system\WCF;

/**
 * Unmutes a user.
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2013 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	system.chat.command.commands
 */
class UnmuteCommand extends \chat\system\command\AbstractRestrictedCommand {
	public $user = null;
	public $time = 0;
	public $suspensionAction = null;
	public $link = '';
	public $room = null;
	
	public function __construct(\chat\system\command\CommandHandler $commandHandler) {
		parent::__construct($commandHandler);
		
		$username = rtrim($commandHandler->getParameters(), ',');
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
		if ($suspension = suspension\Suspension::getSuspensionByUserRoomAndType($this->user, $this->room, suspension\Suspension::TYPE_MUTE)) {
			$action = new suspension\SuspensionAction(array($suspension), 'delete');
			$action->executeAction();
		}
		else {
			throw new \wcf\system\exception\UserInputException('text', WCF::getLanguage()->get('wcf.chat.suspension.notExists'));			
		}
	}
	
	/**
	 * @see	\chat\system\command\IRestrictedChatCommand::checkPermission()
	 */
	public function checkPermission() {
		parent::checkPermission();
		
		$this->room = $this->commandHandler->getRoom();
		$ph = new \chat\system\permission\PermissionHandler();
		if (!$ph->getPermission($this->room, 'mod.can'.ucfirst(str_replace(array('chat\system\command\commands\\Un', 'Command'), '', get_class($this))))) throw new \wcf\system\exception\PermissionDeniedException();
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
			'until' => TIME_NOW + $this->time,
			'type' => str_replace(array('chat\system\command\commands\\', 'command'), '', strtolower(get_class($this)))
		));
	}
}
