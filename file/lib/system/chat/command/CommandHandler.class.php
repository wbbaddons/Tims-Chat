<?php
namespace wcf\system\chat\command;
use \wcf\util\StringUtil;

/**
 * Handles commands
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2012 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.wcf.chat
 * @subpackage	system.chat.command
 */
final class CommandHandler {
	/**
	 * char that indicates a command
	 * @var string
	 */
	const COMMAND_CHAR = '/';
	
	/**
	 * message text
	 * @var string
	 */
	private $text = '';
	
	/**
	 * Initialises the CommandHandler
	 * 
	 * @param	string	$text
	 */
	public function __construct($text) {
		$this->text = $text;
	}
	
	/**
	 * Checks whether the given text is a command.
	 */
	public function isCommand($text = null) {
		if ($text === null) $text = $this->text;
		
		return StringUtil::startsWith($text, static::COMMAND_CHAR);
	}
	
	/**
	 * Returns the whole message.
	 *
	 * @return	string
	 */
	public function getText() {
		return $this->text;
	}
	
	/**
	 * Returns the parameter-string.
	 * 
	 * @return	string
	 */
	public function getParameters() {
		$parts = explode(' ', StringUtil::substring($this->text, StringUtil::length(static::COMMAND_CHAR)), 2);
		
		if (!isset($parts[1])) return '';
		return $parts[1];
	}
	
	/**
	 * Loads the command.
	 */
	public function loadCommand() {
		$parts = explode(' ', StringUtil::substring($this->text, StringUtil::length(static::COMMAND_CHAR)), 2);
		
		if ($this->isCommand($parts[0])) {
			return new commands\PlainCommand($this);
		}
		
		$class = '\wcf\system\chat\command\commands\\'.ucfirst(strtolower($parts[0])).'Command';
		if (!class_exists($class)) {
			throw new NotFoundException();
		}
		
		return new $class($this);
	}
}
