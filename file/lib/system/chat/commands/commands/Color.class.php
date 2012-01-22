<?php
namespace wcf\system\chat\commands\commands;
use \wcf\util\StringUtil;

/**
 * Changes the color of the username
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2011 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	timwolla.wcf.chat
 * @subpackage	system.chat.commands.commands
 */
class Color extends \wcf\system\chat\commands\AbstractCommand {
	const ENABLE_SMILIES = \wcf\system\chat\commands\ICommand::SMILEY_OFF;
	public static $colors = array(
		'red' => 0xFF0000,
		'blue' => 0x0000FF,
		'green' => 0x00FF00,
		'yellow' => 0xFFFF00,
		'black' => 0x000000,
		'white' => 0xFFFFFF,
		'orange' => 0xFFA500,
		'purple' => 0xA020F0,
		'weed' => 0xF5DEB3,
		'pink' => 0xFFC0CB,
		'grey' => 0xBEBEBE,
		'khaki' => 0xF0E68C,
		'lavender' => 0xE6E6FA,
		'maroon' => 0xB03060,
		'gold' => 0xFFD700,
		'navyblue' => 0x000080,
		'royalblue' => 0x4169E1,
		'aquamarine' => 0x7FFFD4,
		'cyan' => 0x00FFFF,
		'magenta' => 0x00FFFF
	);
	
	public function __construct(\wcf\system\chat\commands\CommandHandler $commandHandler) {
		parent::__construct($commandHandler);
		list($color1, $color2) = explode(' ', $commandHandler->getParameters());
		if (isset(self::$colors[$color1])) $color1 = self::$colors[$color1];
		else {
			if (strlen($color1) == 3) $color1 = $color1[0].$color1[0].$color1[1].$color1[1].$color1[2].$color1[2];
			$color1 = hexdec($color1);
		}
		if (isset(self::$colors[$color2])) $color2 = self::$colors[$color2];
		else {
			if (strlen($color2) == 3) $color2 = $color2[0].$color2[0].$color2[1].$color2[1].$color2[2].$color2[2];
			$color2 = hexdec($color2);
		}
		
		\wcf\util\ChatUtil::writeUserData(array('color' => array(1 => $color1, 2 => $color2)));
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
