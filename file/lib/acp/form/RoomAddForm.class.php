<?php
namespace chat\acp\form;
use \wcf\system\exception\UserInputException;
use \wcf\system\language\I18nHandler;
use \wcf\system\WCF;

/**
 * Shows the chatroom add form.
 *
 * @author	Tim Düsterhus
 * @copyright	2010-2013 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	acp.form
 */
class RoomAddForm extends \wcf\form\AbstractForm {
	/**
	 * @see	\wcf\acp\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'chat.acp.menu.link.room.add';
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.chat.canAddRoom');
	
	/**
	 * Title of the room
	 * 
	 * @var	string
	 */
	public $title = '';
	
	/**
	 * Topic of the room
	 * 
	 * @var	string
	 */
	public $topic = '';
	
	/**
	 * @see	\wcf\page\AbstractPage::__construct()
	 */
	public function __run() {
		$this->objectTypeID = \wcf\system\acl\ACLHandler::getInstance()->getObjectTypeID('be.bastelstu.chat.room');
		
		parent::__run();
	}
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();

		I18nHandler::getInstance()->register('title');
		I18nHandler::getInstance()->register('topic');
	}
	
	/**
	 * @see	\wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		I18nHandler::getInstance()->readValues();
		
		if (I18nHandler::getInstance()->isPlainValue('title')) $this->title = I18nHandler::getInstance()->getValue('title');
		if (I18nHandler::getInstance()->isPlainValue('topic')) $this->topic = I18nHandler::getInstance()->getValue('topic');
	}
	
	/**
	 * @see	\wcf\form\IForm::validate()
	 */
	public function validate() {
		parent::validate();
		
		// validate title
		if (!I18nHandler::getInstance()->validateValue('title')) {
			throw new UserInputException('title');
		}
	}
	
	/**
	 * @see	\wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();
		
		// save room
		$this->objectAction = new \chat\data\room\RoomAction(array(), 'create', array('data' => array_merge($this->additionalFields, array(
			'title' => $this->title,
			'topic' => $this->topic
		))));
		$this->objectAction->executeAction();
		$returnValues = $this->objectAction->getReturnValues();
		$roomEditor = new \chat\data\room\RoomEditor($returnValues['returnValues']);
		$roomID = $returnValues['returnValues']->roomID;
		
		if (!I18nHandler::getInstance()->isPlainValue('title')) {
			I18nHandler::getInstance()->save('title', 'chat.room.title'.$roomID, 'chat.room', \chat\util\ChatUtil::getPackageID());
		
			// update title
			$roomEditor->update(array(
				'title' => 'chat.room.title'.$roomID
			));
		}
		
		if (!I18nHandler::getInstance()->isPlainValue('topic')) {
			I18nHandler::getInstance()->save('topic', 'chat.room.topic'.$roomID, 'chat.room', \chat\util\ChatUtil::getPackageID());
		
			// update topic
			$roomEditor->update(array(
				'topic' => 'chat.room.topic'.$roomID
			));
		}
		
		\wcf\system\acl\ACLHandler::getInstance()->save($roomID, $this->objectTypeID);
		\wcf\system\acl\ACLHandler::getInstance()->disableAssignVariables(); 
		\chat\system\permission\PermissionHandler::clearCache();
		
		$this->saved();
		
		// reset values
		$this->topic = $this->title = '';
		I18nHandler::getInstance()->reset();
		
		// show success
		WCF::getTPL()->assign(array(
			'success' => true
		));
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		I18nHandler::getInstance()->assignVariables();
		\wcf\system\acl\ACLHandler::getInstance()->assignVariables($this->objectTypeID);
		
		WCF::getTPL()->assign(array(
			'action' => 'add',
			'title' => $this->title,
			'topic' => $this->topic,
			'objectTypeID' => $this->objectTypeID
		));
	}
}
