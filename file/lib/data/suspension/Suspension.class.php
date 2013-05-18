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
class Suspension extends \chat\data\CHATDatabaseObject {
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
	
	/**
	 * Returns all the suspensions for the specified user (current user if no user was specified).
	 * 
	 * @param	\wcf\data\user\User	$user
	 * @return	array
	 */
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
			while ($suspension = $stmt->fetchObject('\chat\data\suspension\Suspension')) {
				$suspensions[$suspension->roomID][$suspension->type] = $suspension;
			}
			
			\chat\util\ChatUtil::writeUserData(array('suspensions' => $suspensions), $user);
		}
		
		return $suspensions;
	}
	
	/**
	 * Returns the appropriate suspension for user, room and type.
	 * Returns false if no suspension was found.
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
				AND	type = ?";
		
		$parameter = array($user->userID, $type);
		if ($room->roomID) {
			$sql .= " AND roomID = ?";
			$parameter[] = $room->roomID;
		}
		else $sql .= " AND roomID IS NULL";
		
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($parameter);
		$row = $statement->fetchArray();
		if (!$row) return false;
		
		return new self(null, $row);
	}
}
