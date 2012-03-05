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
	 * @see	\wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = '\wcf\data\chat\room\ChatRoom';
	
	/**
	 * Clears the room cache.
	 */
	public static function resetCache() {
		\wcf\system\cache\CacheHandler::getInstance()->clear(WCF_DIR.'cache', 'cache.chatrooms.php');
	}
	
	/**
	 * @see	\wcf\data\DatabaseObjectEditor::deleteAll()
	 */
	public static function deleteAll(array $objectIDs = array()) {
		parent::deleteAll($objectIDs);
		$packageID = \wcf\system\package\PackageDependencyHandler::getInstance()->getPackageID('timwolla.wcf.chat');
		
		WCF::getDB()->beginTransaction();
		foreach ($objectIDs as $objectID) {
			\wcf\system\language\I18nHandler::getInstance()->remove('wcf.chat.room.title'.$objectID, $packageID);
			\wcf\system\language\I18nHandler::getInstance()->remove('wcf.chat.room.topic'.$objectID, $packageID);
		}
		WCF::getDB()->commitTransaction();
		
		return count($objectIDs);
	}
}
