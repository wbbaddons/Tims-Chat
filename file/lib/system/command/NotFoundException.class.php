<?php
namespace chat\system\command;

/**
 * Thrown when a command is not found.
 * 
 * @author 	Tim Düsterhus
 * @copyright	2010-2014 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	system.chat.command
 */
class NotFoundException extends \Exception {
	/**
	 * given command
	 * @var string
	 */
	private $command = '';
	
	public function __construct($command) {
		$this->command = $command;
	}
	
	/**
	 * Returns the given command
	 * 
	 * @return string
	 */
	public function getCommand() {
		return $this->command;
	}
}
