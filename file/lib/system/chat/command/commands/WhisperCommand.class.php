<?php
namespace wcf\system\chat\command\commands;
use \wcf\data\user\User;

/**
 * Whispers a message.
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2012 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.wcf.chat
 * @subpackage	system.chat.command.commands
 */
class WhisperCommand extends \wcf\system\chat\command\AbstractCommand {
	public $enableSmilies = \wcf\system\chat\command\ICommand::SMILEY_USER;
	public $user = null, $message = '';
	
	public function __construct(\wcf\system\chat\command\CommandHandler $commandHandler) {
		parent::__construct($commandHandler);
		
		$parameters = $commandHandler->getParameters();
	
		if (($comma = strpos($parameters, ',')) !== false) {
			$username = substr($parameters, 0, $comma);
			$this->message = substr($parameters, $comma + 1);
		}
		else throw new \wcf\system\chat\command\NotFoundException();
		
		$this->user = User::getUserByUsername($username);
		if (!$this->user->userID) throw new \wcf\system\chat\command\UserNotFoundException($username);
		
		$this->didInit();
	}
	
	/**
	 * @see	\wcf\system\chat\command\ICommand::getType()
	 */
	public function getType() {
		return \wcf\data\chat\message\ChatMessage::TYPE_WHISPER;
	}
	
	/**
	 * @see	\wcf\system\chat\command\ICommand::getMessage()
	 */
	public function getMessage() {
		return serialize(array('message' => $this->message, 'username' => $this->user->username));
	}
	
	/**
	 * @see	\wcf\system\chat\command\ICommand::getReceiver()
	 */
	public function getReceiver() {
		return $this->user->userID;
	}
}
