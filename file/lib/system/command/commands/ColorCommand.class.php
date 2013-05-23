<?php
namespace chat\system\command\commands;
use \wcf\util\StringUtil;

/**
 * Changes the color of the username
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2013 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	system.chat.command.commands
 */
class ColorCommand extends \chat\system\command\AbstractCommand {
	public static $colors = array(
		'red'		=> 0xFF0000,
		'blue'		=> 0x0000FF,
		'green'		=> 0x00FF00,
		'yellow'	=> 0xFFFF00,
		'black'		=> 0x000000,
		'white'		=> 0xFFFFFF,
		'orange'	=> 0xFFA500,
		'purple'	=> 0xA020F0,
		'weed'		=> 0xF5DEB3,
		'pink'		=> 0xFFC0CB,
		'grey'		=> 0xBEBEBE,
		'khaki'		=> 0xF0E68C,
		'lavender'	=> 0xE6E6FA,
		'maroon'	=> 0xB03060,
		'gold'		=> 0xFFD700,
		'navyblue'	=> 0x000080,
		'royalblue'	=> 0x4169E1,
		'aquamarine'	=> 0x7FFFD4,
		'cyan'		=> 0x00FFFF,
		'magenta'	=> 0x00FFFF,
		'oxford'	=> 0xF02D // looks like green
	);
	
	public function __construct(\chat\system\command\CommandHandler $commandHandler) {
		parent::__construct($commandHandler);
		
		try {
			list($color[1], $color[2]) = explode(' ', $commandHandler->getParameters(), 2);
		}
		catch (\wcf\system\exception\SystemException $e) {
			$color[1] = $color[2] = $commandHandler->getParameters();
		}
		
		$regex = new \wcf\system\Regex('^#?([a-f0-9]{3}|[a-f0-9]{6})$', \wcf\system\Regex::CASE_INSENSITIVE);
		foreach ($color as $key => $val) {
			if (isset(self::$colors[$val])) $color[$key] = self::$colors[$val];
			else {
				if (!$regex->match($val)) throw new \chat\system\command\NotFoundException();
				$matches = $regex->getMatches();
				$val = $matches[1];
				if (strlen($val) == 3) $val = $val[0].$val[0].$val[1].$val[1].$val[2].$val[2];
				$color[$key] = hexdec($val);
			}
		}
		\chat\util\ChatUtil::writeUserData(array('color' => $color));
		$this->didInit();
	}
	
	/**
	 * @see	\chat\system\command\ICommand::getType()
	 */
	public function getType() {
		return \chat\data\message\Message::TYPE_INFORMATION;
	}
	
	/**
	 * @see	\chat\system\command\ICommand::getMessage()
	 */
	public function getMessage() {
		return 'color changed';
	}
	
	/**
	 * @see	\chat\system\command\ICommand::getReceiver()
	 */
	public function getReceiver() {
		return \wcf\system\WCF::getUser()->userID;
	}
}
