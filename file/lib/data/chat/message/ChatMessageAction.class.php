<?php
namespace wcf\data\chat\message;

/**
 * Executes message related actions.
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2012 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.wcf.chat
 * @subpackage	data.chat.message
 */
class ChatMessageAction extends \wcf\data\AbstractDatabaseObjectAction {
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = '\wcf\data\chat\message\ChatMessageEditor';
	
	/**
	 * Removes old messages.
	 * 
	 * @return	integer			Number of deleted messages.
	 */
	public function prune() {
		$sql = "SELECT
				".call_user_func(array($this->className, 'getDatabaseTableIndexName'))."
			FROM
				".call_user_func(array($this->className, 'getDatabaseTableName'))."
			WHERE
				time < ?";
		$stmt = \wcf\system\WCF::getDB()->prepareStatement($sql);
		$stmt->execute(array(TIME_NOW - CHAT_LOG_ARCHIVETIME));
		$objectIDs = array();
		while ($objectIDs[] = $stmt->fetchColumn());
		
		return call_user_func(array($this->className, 'deleteAll'), $objectIDs);
	}
}
