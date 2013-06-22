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
	 * Revokes suspensions.
	 */
	public function revoke() {
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
