<?php
namespace chat\system\command;
use \wcf\system\event\EventHandler;

/**
 * Default implementation for restricted commands
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2013 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	system.chat.command
 */
abstract class AbstractRestrictedCommand extends AbstractCommand implements IRestrictedCommand {
	public function __construct(CommandHandler $commandHandler) {
		parent::__construct($commandHandler);
		
		$this->checkPermission();
	}
	
	/**
	 * Fires checkPermission event.
	 * 
	 * @see \chat\system\command\IRestrictedCommand
	 */
	public function checkPermission() {
		EventHandler::getInstance()->fireAction($this, 'checkPermission');
	}
}
