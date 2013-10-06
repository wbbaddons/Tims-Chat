<?php
namespace chat\data\suspension;
use \wcf\system\WCF;

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
	 * @see \wcf\data\AbstractDatabaseObjectAction::$requireACP
	 */
	protected $requireACP = array('revoke');
	
	/**
	 * Validates permissions and parameters
	 */
	public function validateRevoke() {
		WCF::getSession()->checkPermissions((array) 'admin.chat.canManageSuspensions');
		
		$this->parameters['revoker'] = WCF::getUser()->userID;
	}
	
	/**
	 * Revokes suspensions.
	 */
	public function revoke() {
		// TODO: ignore revokes if suspension already is revoked
		if (!isset($this->parameters['revoker'])) {
			$this->parameters['revoker'] = null;
		}
		
		$objectAction = new self($this->objectIDs, 'update', array(
			'data' => array(
				'expires' => TIME_NOW,
				'revoker' => $this->parameters['revoker']
			)
		));
		$objectAction->executeAction();
	}
}
