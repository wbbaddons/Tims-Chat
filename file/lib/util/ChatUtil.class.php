<?php
namespace wcf\util;
use \wcf\system\user\storage\UserStorageHandler;
use \wcf\system\package\PackageDependencyHandler;
use \wcf\system\WCF;

/**
 * Chat utilities
 * 
 * @author	Tim Düsterhus
 * @copyright	2010-2012 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	timwolla.wcf.chat
 * @subpackage	util
 */
class ChatUtil {
	/**
	 * Matches a time-interval with modifiers.
	 * Each part may be optionally separated by a comma
	 * 
	 * @see	\wcf\util\ChatUtil::timeModifier()
	 */
	const TIME_MODIFIER_REGEX = '((?:[0-9]+[s|h|d|w|m|y|S|H|D|W|M|Y]?,?)+)';
	
	public static $serialize = array('color' => true);
	
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
		$string = self::str_split($string);
		if (count($string) === 0) return '';
		
		$r = (int) ((($start >> 16 & 255) - ($end >> 16 & 255)) / (count($string) - 1));
		$g = (int) ((($start >> 8 & 255) - ($end >> 8 & 255)) / (count($string) - 1));
		$b = (int) ((($start & 255) - ($end & 255)) / (count($string) - 1));
		
		$result = '';
		for ($i = 0, $max = count($string); $i < $max; $i++) {
			$result .= '<span style="color:rgb('.(($start >> 16 & 255) - $i * $r).','.(($start >> 8 & 255) - $i * $g).','.(($start & 255) - $i * $b).')">'.StringUtil::encodeHTML($string[$i]).'</span>'; 
		}
		
		return $result;
	}
	
	/**
	 * Reads user data.
	 *
	 * @param	string $field
	 * @return	mixed
	 */
	public static function readUserData($field) {
		$ush = UserStorageHandler::getInstance();
		$packageID = PackageDependencyHandler::getPackageID('timwolla.wcf.chat');
		
		// load storage
		$ush->loadStorage(array(WCF::getUser()->userID), $packageID);
		$data = $ush->getStorage(array(WCF::getUser()->userID), $field, $packageID);
		
		if ($data[WCF::getUser()->userID] === null) {
			switch ($field) {
				case 'color':
					$data[WCF::getUser()->userID] = array(1 => self::getRandomNumber(), 2 => self::getRandomNumber() * 0xFFFF);
				break;
			}
			static::writeUserData(array($field => $data[WCF::getUser()->userID]));
			
			return $data[WCF::getUser()->userID];
		}
		
		if (isset(static::$serialize[$field])) return unserialize($data[WCF::getUser()->userID]);
		else return $data[WCF::getUser()->userID];
	}
	
	/**
	 * Returns a random number.
	 * 
	 * @return	integer
	 */
	public static /* int */ getRandomNumber() {
		return 4; // chosen by a fair dice roll
			  // guaranteed to be random
	}
	
	/**
	 * Writes user data
	 * 
	 * @param	array $data
	 */
	public static function writeUserData(array $data) {
		$ush = UserStorageHandler::getInstance();
		$packageID = PackageDependencyHandler::getPackageID('timwolla.wcf.chat');
		
		foreach($data as $key => $value) {
			$ush->update(WCF::getUser()->userID, $key, (isset(static::$serialize[$key])) ? serialize($value) : $value, $packageID);
		}
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
		for ($i = 0, $max = StringUtil::length($string); $i < $max; $i += $length) {
			$result[] = StringUtil::substring($string, $i, $length);
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
		$regex = new \wcf\system\Regex('([0-9]+[s|h|d|w|m|y]?)', \wcf\system\Regex::CASE_INSENSITIVE);
		if (!$regex->match($time, true)) return 0;
		$matches = $regex->getMatches();
		
		$result = 0;
		foreach ($matches[1] as $time) {
			// 60 seconds a minute
			$multiplier = 60;
			$modifier = substr($time, -1);
			
			switch ($modifier) {
				case 'y':
				case 'Y':
					// twelve months a year
					$multiplier *= 12;
				case 'm':
				case 'M':
					// about 4.3 weeks per month
					$multiplier *= 4.34812141;
				case 'w':
				case 'W':
					// seven days a weeks
					$multiplier *= 7;
				case 'd':
				case 'D':
					// 24 hours a day
					$multiplier *= 24;
				case 'h':
				case 'H':
					// 60 minutes an hour
					$multiplier *= 60;
					$time = substr($time, 0, -1);
				break;
				case 's':
				case 'S':
					// 60 seconds per minute
					$multiplier /= 60;
					$time = substr($time, 0, -1);
			}
			
			$result += $time * $multiplier;
		}
		
		return (int) round($result, 0);
	}
}
