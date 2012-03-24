<?php
namespace wcf\system\chat\commands;
use \wcf\system\event\EventHandler;

/**
 * Default implementation for commands.
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2012 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	timwolla.wcf.chat
 * @subpackage	system.chat.commands
 */
abstract class AbstractCommand implements ICommand {
	public $commandHandler = null;
	public $enableHTML = 0;
	
	public function __construct(CommandHandler $commandHandler) {
		EventHandler::getInstance()->fireAction($this, 'shouldInit');
		$this->commandHandler = $commandHandler;
	}
	
	public function didInit() {
		EventHandler::getInstance()->fireAction($this, 'didInit');
	}
	
	public function getReceiver() {
		return null;
	}
}
