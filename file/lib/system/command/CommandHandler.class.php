<?php
namespace chat\system\command;
use \wcf\util\StringUtil;

/**
 * Handles commands
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2014 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	system.chat.command
 */
final class CommandHandler {
	/**
	 * char that indicates a command
	 * @var	string
	 */
	const COMMAND_CHAR = '/';
	
	/**
	 * message text
	 * @var	string
	 */
	private $text = '';
	
	/**
	 * current room
	 * @var	\chat\data\room\Room
	 */
	private $room = null;
	
	/**
	 * Initialises the CommandHandler
	 * 
	 * @param	string			$text
	 * @param	\chat\data\room\Room	$room
	 */
	public function __construct($text, \chat\data\room\Room $room = null) {
		$this->text = $text;
		$this->room = $room;
		
		$aliases = self::getAliasMap();
		foreach ($aliases as $search => $replace) {
			$this->text = \wcf\system\Regex::compile('^'.preg_quote(self::COMMAND_CHAR.$search).'( |$)')->replace($this->text, self::COMMAND_CHAR.$replace.' ');
		}
		
		$this->text = \wcf\system\Regex::compile('^//')->replace($this->text, '/plain ');
	}
	
	/**
	 * Returns the alias map. Key is the alias, value is the target.
	 * 
	 * @return	array<string>
	 */
	public static function getAliasMap() {
		try {
			$result = array();
			foreach (explode("\n", StringUtil::unifyNewlines(StringUtil::toLowerCase(CHAT_COMMAND_ALIASES))) as $line) {
				list($key, $val) = explode(':', $line, 2);
				
				$result[$key] = $val;
			}
			
			return $result;
		}
		catch (\wcf\system\exception\SystemException $e) {
			throw new \wcf\system\exception\SystemException("Invalid alias specified: '".$line."'");
		}
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
	 * Returns the current room.
	 * 
	 * @return	\chat\data\room\Room
	 */
	public function getRoom() {
		return $this->room;
	}
	
	/**
	 * Returns the parameter-string.
	 * 
	 * @return	string
	 */
	public function getParameters() {
		$parts = explode(' ', mb_substr($this->text, mb_strlen(static::COMMAND_CHAR)), 2);
		
		if (!isset($parts[1])) return '';
		return $parts[1];
	}
	
	/**
	 * Loads the command.
	 */
	public function loadCommand() {
		$parts = explode(' ', mb_substr($this->text, mb_strlen(static::COMMAND_CHAR)), 2);
		
		$class = '\chat\system\command\commands\\'.ucfirst(strtolower($parts[0])).'Command';
		if (!class_exists($class)) {
			throw new NotFoundException($parts[0]);
		}
		
		return new $class($this);
	}
}
