<?php
namespace wcf\system\chat\commands;
use \wcf\system\event\EventHandler;

/**
 * Default implementation for restricted commands
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2011 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	timwolla.wcf.chat
 * @subpackage	system.chat.commands
 */
abstract class AbstractRestrictedCommand extends AbstractCommand implements IRestrictedCommand {
	public function __construct(CommandHandler $commandHandler) {
		parent::__construct($commandHandler);
		
		$this->checkPermission();
	}
	
	public function checkPermission() {
		EventHandler::getInstance()->fireAction($this, 'checkPermission');
	}
}
