<?php
namespace chat\data\suspension;

/**
 * Executes chat-suspension-related actions.
 * 
 * @author 	Tim Düsterhus
 * @copyright	2010-2013 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	data.suspension
 */
class SuspensionAction extends \wcf\data\AbstractDatabaseObjectAction {
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = '\chat\data\suspension\SuspensionEditor';
	
	/**
	 * Deletes expired suspensions.
	 * 
	 * @return	integer		Number of deleted suspensions
	 */
	public function prune() {
		$sql = "SELECT
				".call_user_func(array($this->className, 'getDatabaseTableIndexName'))."
			FROM
				".call_user_func(array($this->className, 'getDatabaseTableName'))."
			WHERE
				time < ?";
		$stmt = \wcf\system\WCF::getDB()->prepareStatement($sql);
		$stmt->execute(array(TIME_NOW));
		$objectIDs = array();
		
		while ($objectID = $stmt->fetchColumn()) $objectIDs[] = $objectID;
		
		return call_user_func(array($this->className, 'deleteAll'), $objectIDs);
	}
}
