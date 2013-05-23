<?php
namespace chat\data\room;
use \wcf\system\WCF;

/**
 * Provides functions to edit chat rooms.
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2013 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	data.room
 */
class RoomEditor extends \wcf\data\DatabaseObjectEditor implements \wcf\data\IEditableCachedObject {
	/**
	 * @see	\wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = '\chat\data\room\Room';
	
	
	/**
	 * @see	\wcf\data\DatabaseObjectEditor::deleteAll()
	 */
	public static function deleteAll(array $objectIDs = array()) {
		WCF::getDB()->beginTransaction();
		foreach ($objectIDs as $objectID) {
			\wcf\system\language\I18nHandler::getInstance()->remove('chat.room.title'.$objectID);
			\wcf\system\language\I18nHandler::getInstance()->remove('chat.room.topic'.$objectID);
		}
		
		$sql = "SELECT
				showOrder
			FROM
				chat".WCF_N."_room
			WHERE
				roomID = ?
			FOR UPDATE";
		$select = WCF::getDB()->prepareStatement($sql);
		
		$sql = "UPDATE
				chat".WCF_N."_room
			SET
				showOrder = showOrder - 1
			WHERE
				showOrder > ?";
		$update = WCF::getDB()->prepareStatement($sql);
		
		foreach ($objectIDs as $objectID) {
			$select->execute(array($objectID));
			$update->execute(array($select->fetchColumn()));
		}
		
		$return = parent::deleteAll($objectIDs);
		WCF::getDB()->commitTransaction();
		return $return;
	}
	
	/**
	 * Clears the room cache.
	 */
	public static function resetCache() {
		\chat\system\cache\builder\RoomCacheBuilder::getInstance()->reset();
		\chat\system\cache\builder\PermissionCacheBuilder::getInstance()->reset();
	}
}
