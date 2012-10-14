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
 * @package	be.bastelstu.wcf.chat
 * @subpackage	util
 */
final class ChatUtil {
	/**
	 * Matches a time-interval with modifiers.
	 * Each part may be optionally separated by a comma
	 * 
	 * @see	\wcf\util\ChatUtil::timeModifier()
	 */
	const TIME_MODIFIER_REGEX = '((?:[0-9]+[s|h|d|w|m|y|S|H|D|W|M|Y]?,?)+)';
	
	/**
	 * Package identifier of Tims Chat.
	 * 
	 * @var	string
	 */
	const PACKAGE_IDENTIFIER = 'be.bastelstu.wcf.chat';
	
	/**
	 * Which user-storage-keys need serialization.
	 * The value should always be true.
	 * 
	 * @var array<boolean>
	 */
	private static $serialize = array('color' => true, 'suspensions' => true);
	
	/**
	 * Cached packageID of Tims Chat.
	 * 
	 * @var	integer
	 */
	private static $packageID = null;
	
	/**
	 * Fetches the userIDs of died users.
	 * 
	 * @return array
	 */
	public static function getDiedUsers() {
		$packageID = \wcf\util\ChatUtil::getPackageID();
		if (self::nodePushRunning()) {
			$time = TIME_NOW - 120;
		}
		else {
			$time = TIME_NOW;
		}
		
		$sql = "SELECT
				r.userID, r.fieldValue AS roomID
			FROM
				wcf".WCF_N."_user_storage r
			LEFT JOIN
				wcf".WCF_N."_user_storage a
				ON		a.userID = r.userID 
					AND	a.field = ? 
					AND	a.packageID = ?
			WHERE
					r.field = ?
				AND	r.packageID = ?
				AND	r.fieldValue IS NOT NULL
				AND	(
						a.fieldValue < ?
						OR a.fieldValue IS NULL
				)";
		$stmt = WCF::getDB()->prepareStatement($sql);
		$stmt->execute(array('lastActivity', $packageID, 'roomID', $packageID, $time - 30));
		$users = array();
		while ($users[] = $stmt->fetchArray());
		
		return $users;
	}
	/**
	 * Returns the packageID of Tims Chat.
	 */
	public static function getPackageID() {
		if (self::$packageID === null) {
			self::$packageID = PackageDependencyHandler::getInstance()->getPackageID(self::PACKAGE_IDENTIFIER);
		}
		
		return self::$packageID;
	}
	
	/**
	 * Returns a random number.
	 * 
	 * @return	integer
	 */
	public static function /* int */ getRandomNumber() {
		return 4; // chosen by a fair dice roll
			  // guaranteed to be random
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
	 * Checks whether nodePush is running.
	 * 
	 * @return	boolean
	 */
	public static function nodePushRunning() {
		if (!CHAT_SOCKET_IO_PATH) return false;
		if (!file_exists(WCF_DIR.'acp/be.bastelstu.wcf.chat.nodePush/data.sock')) return false;
		if (!is_writable(WCF_DIR.'acp/be.bastelstu.wcf.chat.nodePush/data.sock')) return false;
		
		return true;
	}
	
	/**
	 * Reads user data.
	 *
	 * @param	string 			$field
	 * @param	\wcf\data\user\User	$user
	 * @return	mixed
	 */
	public static function readUserData($field, \wcf\data\user\User $user = null) {
		if ($user === null) $user = WCF::getUser();
		$ush = UserStorageHandler::getInstance();
		$packageID = self::getPackageID();
		
		// load storage
		$ush->loadStorage(array($user->userID), $packageID);
		$data = $ush->getStorage(array($user->userID), $field, $packageID);
		
		if ($data[$user->userID] === null) {
			switch ($field) {
				case 'color':
					$data[$user->userID] = array(1 => self::getRandomNumber(), 2 => self::getRandomNumber() * 0xFFFF);
				break;
			}
			if ($data[$user->userID] !== null) static::writeUserData(array($field => $data[$user->userID]));
			
			return $data[$user->userID];
		}
		
		if (isset(static::$serialize[$field])) return unserialize($data[$user->userID]);
		else return $data[$user->userID];
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
	
	/**
	 * Writes user data
	 * @param	array $data
	 * @param	\wcf\data\user\User	$user
	 */
	public static function writeUserData(array $data, \wcf\data\user\User $user = null) {
		if ($user === null) $user = WCF::getUser();
		$ush = UserStorageHandler::getInstance();
		$packageID = self::getPackageID();
		
		foreach($data as $key => $value) {
			$ush->update($user->userID, $key, (isset(static::$serialize[$key])) ? serialize($value) : $value, $packageID);
		}
	}
	
	/**
	 * Disables the constructor.
	 */
	private function __construct() { }
}
