<?php
namespace wcf\data\chat\room;
use \wcf\system\WCF;

/**
 * Provides functions to edit chat rooms.
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2012 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.wcf.chat
 * @subpackage	data.chat.room
 */
class ChatRoomEditor extends \wcf\data\DatabaseObjectEditor implements \wcf\data\IEditableCachedObject {
	/**
	 * @see	\wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = '\wcf\data\chat\room\ChatRoom';
	
	
	/**
	 * @see	\wcf\data\DatabaseObjectEditor::deleteAll()
	 */
	public static function deleteAll(array $objectIDs = array()) {
		WCF::getDB()->beginTransaction();
		foreach ($objectIDs as $objectID) {
			\wcf\system\language\I18nHandler::getInstance()->remove('wcf.chat.room.title'.$objectID);
			\wcf\system\language\I18nHandler::getInstance()->remove('wcf.chat.room.topic'.$objectID);
		}
		
		$sql = "SELECT
				position
			FROM
				wcf".WCF_N."_chat_room
			WHERE
				roomID = ?
			FOR UPDATE";
		$select = WCF::getDB()->prepareStatement($sql);
		
		$sql = "UPDATE
				wcf".WCF_N."_chat_room
			SET
				position = position - 1
			WHERE
				position > ?";
		$update = WCF::getDB()->prepareStatement($sql);
		
		foreach ($objectIDs as $objectID) {
			$select->execute(array($objectID));
			$update->execute(array($select->fetchColumn()));
		}
		
		// The transaction is being committed in parent::deleteAll()
		// The beginTransaction() call in there is simply ignored.
		return parent::deleteAll($objectIDs);
	}
	
	/**
	 * Clears the room cache.
	 */
	public static function resetCache() {
		\wcf\system\cache\CacheHandler::getInstance()->clear(WCF_DIR.'cache', 'cache.chatrooms.php');
	}
}
