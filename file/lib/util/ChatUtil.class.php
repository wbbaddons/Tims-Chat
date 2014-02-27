<?php
namespace chat\util;
use \wcf\data\package\PackageCache;
use \wcf\system\user\storage\UserStorageHandler;
use \wcf\system\WCF;

/**
 * Chat utilities
 * 
 * @author	Tim Düsterhus
 * @copyright	2010-2014 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	util
 */
final class ChatUtil {
	/**
	 * Matches a time-interval with modifiers.
	 * Each part may be optionally separated by a comma
	 * 
	 * @see	\chat\util\ChatUtil::timeModifier()
	 */
	const TIME_MODIFIER_REGEX = '((?:[0-9]+[s|h|d|w|m|y|S|H|D|W|M|Y]?,?)+)';
	
	/**
	 * Package identifier of Tims Chat.
	 * 
	 * @var	string
	 */
	const PACKAGE_IDENTIFIER = 'be.bastelstu.chat';
	
	/**
	 * Cached packageID of Tims Chat.
	 * 
	 * @var	integer
	 */
	private static $packageID = null;
	
	/**
	 * Returns the packageID of Tims Chat.
	 */
	public static function getPackageID() {
		if (self::$packageID === null) {
			self::$packageID = PackageCache::getInstance()->getPackageID(self::PACKAGE_IDENTIFIER);
		}
		
		return self::$packageID;
	}
	
	/**
	 * Creates a gradient out of two colors represented by an integer.
	 * The first byte is red, the second byte is green, the third one is blue.
	 * The numbers can be easily expressed in hexadecimal notation: 0xFF0000 being red.
	 *
	 * @param	string	$string
	 * @param	integer	$start
	 * @param	integer	$end
	 * @return	string
	 */
	public static function gradient($string, $start, $end) {
		if (($length = mb_strlen($string)) === 0) return '';
		
		if ($start === $end) {
			return '<span style="color:rgb('.($start >> 16 & 255).','.($start >> 8 & 255).','.($start & 255).')">'.\wcf\util\StringUtil::encodeHTML($string).'</span>';
		}
		
		$r = (int) ((($start >> 16 & 255) - ($end >> 16 & 255)) / ($length - 1));
		$g = (int) ((($start >> 8 & 255) - ($end >> 8 & 255)) / ($length - 1));
		$b = (int) ((($start & 255) - ($end & 255)) / ($length - 1));
		
		$result = '';
		$string = self::str_split($string);
		for ($i = 0; $i < $length; $i++) {
			$result .= '<span style="color:rgb('.(($start >> 16 & 255) - $i * $r).','.(($start >> 8 & 255) - $i * $g).','.(($start & 255) - $i * $b).')">'.\wcf\util\StringUtil::encodeHTML($string[$i]).'</span>'; 
		}
		
		return $result;
	}
	
	/**
	 * Splits a string into smaller chunks.
	 * UTF-8 safe version of str_split().
	 *
	 * @param	string		$string
	 * @param	integer		$length
	 * @return	array<string>
	 */
	public static function str_split($string, $length = 1) {
		$result = array();
		for ($i = 0, $max = mb_strlen($string); $i < $max; $i += $length) {
			$result[] = mb_substr($string, $i, $length);
		}
		return $result;
	}
	
	/**
	 * Creates an interval out of a string with modifiers.
	 * Modifiers may be mixed. Valid modifiers are: _s_econd, _h_our, _d_ay, _w_week, _m_onth, _y_ear
	 * '2' -> 2 minutes
	 * '2h' -> 2 hours
	 * '1y12m2w3d12h' -> 1 year, 12 months, 2 weeks, 3 days, 12 hours
	 *
	 * @param 	string	 	$time
	 * @return	integer
	 */
	public static function timeModifier($time) {
		$regex = new \wcf\system\Regex('([0-9]+)([shdwmy]?)', \wcf\system\Regex::CASE_INSENSITIVE);
		if (!$regex->match($time, true, \wcf\system\Regex::ORDER_MATCH_BY_SET)) return 0;
		$matches = $regex->getMatches();
		
		$result = '';
		foreach ($matches as $match) {
			list(, $time, $modifier) = $match;
			
			switch ($modifier) {
				case 'y':
				case 'Y':
					$result .= '+'.$time.'year';
				break;
				case 'm':
				case 'M':
					$result .= '+'.$time.'month';
				break;
				case 'w':
				case 'W':
					$result .= '+'.$time.'week';
				break;
				case 'd':
				case 'D':
					$result .= '+'.$time.'day';
				break;
				case 'h':
				case 'H':
					$result .= '+'.$time.'hour';
				break;
				case 's':
				case 'S':
					$result .= '+'.$time.'second';
				break;
				default:
					$result .= '+'.$time.'minute';
			}
		}
		
		return $result;
	}
	
	/**
	 * Disables the constructor.
	 */
	private function __construct() { }
}
