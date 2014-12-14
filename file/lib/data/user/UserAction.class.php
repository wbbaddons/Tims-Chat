<?php
namespace chat\data\user;
use wcf\system\WCF;

/**
 * User related chat actions.
 * 
 * @author 	Maximilian Mader
 * @copyright	2010-2014 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	data.user
 */
class UserAction extends \wcf\data\AbstractDatabaseObjectAction {
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\user\UserEditor';
	
	/**
	 * Validates updating of chat user options
	 */
	public function validateUpdateOption() {
		$this->readString('optionName');
		$this->readBoolean('optionValue');
		
		if (!preg_match('~^chat[A-Z]~', $this->parameters['optionName'])) throw new \wcf\system\exception\UserInputException('optionName');
		
		$this->optionID = \wcf\data\user\User::getUserOptionID($this->parameters['optionName']);
		
		if (!$this->optionID) throw new \wcf\system\exception\UserInputException('optionName');
	}
	
	/**
	 * Updates chat user options
	 */
	public function updateOption() {
		$userAction = new \wcf\data\user\UserAction(array(WCF::getUser()), 'update', array(
			'options' => array(
				$this->optionID => $this->parameters['optionValue'] ? 1 : 0
			)
		));
		$userAction->executeAction();
	}
}
