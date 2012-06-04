<?php
namespace wcf\system\chat\command;

/**
 * Thrown when a user is not found.
 * 
 * @author 	Tim Düsterhus
 * @copyright	2010-2012 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.wcf.chat
 * @subpackage	system.chat.command
 */
class UserNotFoundException extends \Exception {
	private $username = '';
	
	public function __construct($username) {
		$this->username = $username;
	}
	
	public function getUsername() {
		return $this->username;
	}
}