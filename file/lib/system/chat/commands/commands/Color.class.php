<?php
namespace wcf\system\chat\commands\commands;
use \wcf\util\StringUtil;

/**
 * Changes the color of the username
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2012 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	timwolla.wcf.chat
 * @subpackage	system.chat.commands.commands
 */
class Color extends \wcf\system\chat\commands\AbstractCommand {
	public $enableSmilies = \wcf\system\chat\commands\ICommand::SMILEY_OFF;
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
	
	public function __construct(\wcf\system\chat\commands\CommandHandler $commandHandler) {
		parent::__construct($commandHandler);
		try {
			list($color[1], $color[2]) = explode(' ', $commandHandler->getParameters());
		}
		catch (\wcf\system\exception\SystemException $e) {
			$color[1] = $color[2] = $commandHandler->getParameters();
		}
		
		$regex = new \wcf\system\Regex('^#?([a-f0-9]{3}|[a-f0-9]{6})$', \wcf\system\Regex::CASE_INSENSITIVE);
		foreach ($color as $key => $val) {
			if (isset(self::$colors[$val])) $color[$key] = self::$colors[$val];
			else {
				if (!$regex->match($val)) throw new \wcf\system\chat\commands\NotFoundException();
				$matches = $regex->getMatches();
				$val = $matches[1];
				if (strlen($val) == 3) $val = $val[0].$val[0].$val[1].$val[1].$val[2].$val[2];
				$color[$key] = hexdec($val);
			}
		}
		\wcf\util\ChatUtil::writeUserData(array('color' => $color));
	}
	
	/**
	 * @see	\wcf\system\chat\commands\ICommand::getType()
	 */
	public function getType() {
		return \wcf\data\chat\message\ChatMessage::TYPE_INFORMATION;
	}
	
	/**
	 * @see	\wcf\system\chat\commands\ICommand::getMessage()
	 */
	public function getMessage() {
		return 'color changed';
	}
	
	/**
	 * @see	\wcf\system\chat\commands\ICommand::getReceiver()
	 */
	public function getReceiver() {
		return \wcf\system\WCF::getUser()->userID;
	}
}
