<?php
namespace wcf\data\chat\suspension;
use \wcf\system\WCF;

/**
 * Represents a chat suspension.
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2012 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.wcf.chat
 * @subpackage	data.chat.suspension
 */
class ChatSuspension extends \wcf\data\DatabaseObject {
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'chat_suspension';
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'suspensionID';
	
	const TYPE_MUTE = 1;
	const TYPE_BAN = 2;
	
	public static function getSuspensionsForUser(\wcf\data\user\User $user = null) {
		if ($user === null) $user = WCF::getUser();
		$suspensions = \wcf\util\ChatUtil::readUserData('suspensions', $user);
		if ($suspensions === null) {
			$sql = "SELECT
					*
				FROM
					wcf".WCF_N."_chat_suspension
				WHERE
						userID = ?
					AND	time > ?";
			$stmt = WCF::getDB()->prepareStatement($sql);
			$stmt->execute(array($user->userID, TIME_NOW));
			
			$suspensions = array();
			while ($row = $stmt->fetchArray()) {
				$suspensions[$row['roomID']][$row['type']] = $row;
			}
			
			\wcf\util\ChatUtil::writeUserData(array('suspensions' => $suspensions), $user);
		}
		
		return $suspensions;
	}
	
	/**
	 * Returns the appropriate suspension for user, room and type.
	 * 
	 * @param	\wcf\data\user\User		$user
	 * @param	\wcf\data\chat\room\ChatRoom	$room
	 * @param	integer				$type
	 * @return	\wcf\data\chat\suspension\ChatSuspension
	 */
	public static function getSuspensionByUserRoomAndType(\wcf\data\user\User $user, \wcf\data\chat\room\ChatRoom $room, $type) {
		$sql = "SELECT
				*
			FROM
				wcf".WCF_N."_chat_suspension
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
