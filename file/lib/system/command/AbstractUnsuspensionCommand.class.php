<?php
namespace chat\system\command;
use \chat\data\suspension;
use \chat\util\ChatUtil;
use \wcf\data\user\User;
use \wcf\system\WCF;

/**
 * Default implementation for suspension commands
 * 
 * @author 	Tim Düsterhus
 * @copyright	2010-2014 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	system.chat.command
 */
abstract class AbstractUnsuspensionCommand extends AbstractRestrictedCommand {
	public $user = null;
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
		if (static::IS_GLOBAL) $room = new \chat\data\room\Room(null, array('roomID' => null));
		else $room = $this->room;
		
		if ($suspension = suspension\Suspension::getSuspensionByUserRoomAndType($this->user, $room, static::SUSPENSION_TYPE)) {
			$action = new suspension\SuspensionAction(array($suspension), 'revoke', array(
				'revoker' => WCF::getUser()->userID
			));
			$action->executeAction();
		}
		else {
			throw new \wcf\system\exception\UserInputException('text', WCF::getLanguage()->get('chat.suspension.notExists'));
		}
	}
	
	/**
	 * @see	\chat\system\command\IRestrictedChatCommand::checkPermission()
	 */
	public function checkPermission() {
		parent::checkPermission();
		
		$this->room = $this->commandHandler->getRoom();
		if (WCF::getSession()->getPermission('admin.chat.canManageSuspensions')) return;
		
		$ph = new \chat\system\permission\PermissionHandler();
		if (static::IS_GLOBAL) {
			WCF::getSession()->checkPermission((array) 'mod.chat.canG'.static::SUSPENSION_TYPE);
		}
		else {
			if (!WCF::getSession()->getPermission('mod.chat.canG'.static::SUSPENSION_TYPE)) {
				if (!$ph->getPermission($this->room, 'mod.can'.ucfirst(static::SUSPENSION_TYPE))) {
					throw new \wcf\system\exception\PermissionDeniedException();
				}
			}
		}
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
			'type' => 'un'.(static::IS_GLOBAL ? 'g' : '').static::SUSPENSION_TYPE,
		));
	}
}
