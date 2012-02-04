<?php
namespace wcf\data\chat\room;
use \wcf\system\WCF;

/**
 * Provides functions to edit chat rooms.
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2012 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	timwolla.wcf.chat
 * @subpackage	data.chat.room
 */
class ChatRoomEditor extends \wcf\data\DatabaseObjectEditor implements \wcf\data\IEditableCachedObject {
	/**
	 * @see	wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = '\wcf\data\chat\room\ChatRoom';
	
	/**
	 * Clears the room cache.
	 */
	public static function resetCache() {
		ChatRoom::getCache();
		\wcf\system\cache\CacheHandler::getInstance()->clearResource('chatrooms');
	}
	
	/**
	 * @see \wcf\data\DatabaseObjectEditor::deleteAll()
	 */
	public static function deleteAll(array $objectIDs = array()) {
		parent::deleteAll($objectIDs);
		$packageID = \wcf\system\package\PackageDependencyHandler::getPackageID('timwolla.wcf.chat');
		$sql = "DELETE FROM wcf".WCF_N."_language_item
			WHERE languageItem = ? AND packageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		
		WCF::getDB()->beginTransaction();
		foreach ($objectIDs as $objectID) {
			$statement->execute(array('wcf.chat.room.title.room'.$objectID, $packageID));
			$statement->execute(array('wcf.chat.room.topic.room'.$objectID, $packageID));
		}
		WCF::getDB()->commitTransaction();
		
		return count($objectIDs);
	}
}
