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
	 * Revokes expired suspensions.
	 * 
	 * @return	array<integer>	Revoked suspensions
	 */
	public function prune() {
		$sql = "SELECT
				".call_user_func(array($this->className, 'getDatabaseTableIndexName'))."
			FROM
				".call_user_func(array($this->className, 'getDatabaseTableName'))."
			WHERE
				expires < ?";
		$stmt = \wcf\system\WCF::getDB()->prepareStatement($sql);
		$stmt->execute(array(TIME_NOW));
		$objectIDs = array();
		
		while ($objectID = $stmt->fetchColumn()) $objectIDs[] = $objectID;
		
		$suspensionAction = new self($objectIDs, 'revoke');
		$suspensionAction->executeAction();
		
		return $objectIDs;
	}
	
	/**
	 * Revokes suspensions.
	 */
	public function revoke() {
		if (!isset($this->parameters['revoker'])) {
			$this->parameters['revoker'] = null;
		}
		
		$objectAction = new self($this->objectIDs, 'update', array(
			'data' => array(
				'revoked' => 1,
				'revoker' => $this->parameters['revoker']
			)
		));
		$objectAction->executeAction();
	}
}
