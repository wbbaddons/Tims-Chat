<?php
namespace chat\data\suspension;
use \wcf\system\WCF;

/**
 * Represents a chat suspension.
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2013 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	data.suspension
 */
class Suspension extends \wcf\data\DatabaseObject {
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'suspension';
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'suspensionID';
	
	const TYPE_MUTE = 1;
	const TYPE_BAN = 2;
	
	public static function getSuspensionsForUser(\wcf\data\user\User $user = null) {
		if ($user === null) $user = WCF::getUser();
		$suspensions = \chat\util\ChatUtil::readUserData('suspensions', $user);
		if ($suspensions === null) {
			$sql = "SELECT
					*
				FROM
					chat".WCF_N."_suspension
				WHERE
						userID = ?
					AND	time > ?";
			$stmt = WCF::getDB()->prepareStatement($sql);
			$stmt->execute(array($user->userID, TIME_NOW));
			
			$suspensions = array();
			while ($row = $stmt->fetchArray()) {
				$suspensions[$row['roomID']][$row['type']] = $row;
			}
			
			\chat\util\ChatUtil::writeUserData(array('suspensions' => $suspensions), $user);
		}
		
		return $suspensions;
	}
	
	/**
	 * Returns the appropriate suspension for user, room and type.
	 * 
	 * @param	\wcf\data\user\User	$user
	 * @param	\chat\data\room\Room	$room
	 * @param	integer			$type
	 * @return	\chat\data\suspension\Suspension
	 */
	public static function getSuspensionByUserRoomAndType(\wcf\data\user\User $user, \chat\data\room\Room $room, $type) {
		$sql = "SELECT
				*
			FROM
				chat".WCF_N."_suspension
			WHERE	
					userID = ?
				AND	roomID = ?
				AND	type = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($user->userID, $room->roomID, $type));
		$row = $statement->fetchArray();
		if(!$row) $row = array();
		
		return new self(null, $row);
	}
}
