<?php
namespace chat\data\message;

/**
 * Executes message related actions.
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2013 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	chat.message
 */
class MessageAction extends \wcf\data\AbstractDatabaseObjectAction {
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = '\chat\data\message\MessageEditor';
	
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
