<?php
namespace wcf\system\chat\commands\commands;
use \wcf\util\StringUtil;

/**
 * Marks the user as away.
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2012 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	timwolla.wcf.chat
 * @subpackage	system.chat.commands.commands
 */
class Away extends \wcf\system\chat\commands\AbstractCommand {
	public $enableSmilies = \wcf\system\chat\commands\ICommand::SMILEY_OFF;
	
	public function __construct(\wcf\system\chat\commands\CommandHandler $commandHandler) {
		parent::__construct($commandHandler);
		
		\wcf\util\ChatUtil::writeUserData(array('away' => $commandHandler->getParameters()));
		$this->didInit();
	}
	
	/**
	 * @see	\wcf\system\chat\commands\ICommand::getType()
	 */
	public function getType() {
		return \wcf\data\chat\message\ChatMessage::TYPE_AWAY;
	}
	
	/**
	 * @see	\wcf\system\chat\commands\ICommand::getMessage()
	 */
	public function getMessage() {
		return $this->commandHandler->getParameters();
	}
	
	/**
	 * @see	\wcf\system\chat\commands\ICommand::getReceiver()
	 */
	public function getReceiver() {
		return \wcf\system\WCF::getUser()->userID;
	}
}
