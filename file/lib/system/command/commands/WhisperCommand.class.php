<?php
namespace chat\system\command\commands;
use \wcf\data\user\User;

/**
 * Whispers a message.
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2013 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	system.chat.command.commands
 */
class WhisperCommand extends \chat\system\command\AbstractCommand {
	public $enableSmilies = self::SETTING_USER;
	public $user = null, $message = '';
	
	public function __construct(\chat\system\command\CommandHandler $commandHandler) {
		parent::__construct($commandHandler);
		
		$parameters = $commandHandler->getParameters();
	
		if (($comma = strpos($parameters, ',')) !== false) {
			$username = substr($parameters, 0, $comma);
			$this->message = substr($parameters, $comma + 1);
		}
		else throw new \chat\system\command\NotFoundException();
		
		$this->user = User::getUserByUsername($username);
		if (!$this->user->userID) throw new \chat\system\command\UserNotFoundException($username);
		
		$this->didInit();
	}
	
	/**
	 * @see	\chat\system\command\ICommand::getType()
	 */
	public function getType() {
		return \chat\data\message\Message::TYPE_WHISPER;
	}
	
	/**
	 * @see	\chat\system\command\ICommand::getMessage()
	 */
	public function getMessage() {
		return serialize(array('message' => $this->message, 'username' => $this->user->username));
	}
	
	/**
	 * @see	\chat\system\command\ICommand::getReceiver()
	 */
	public function getReceiver() {
		return $this->user->userID;
	}
}
