<?php
namespace chat\acp\form;
use \wcf\system\language\I18nHandler;
use \wcf\system\WCF;

/**
 * Shows the chatroom edit form.
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2013 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	acp.form
 */
class RoomEditForm extends RoomAddForm {
	/**
	 * @see	\wcf\page\AbstractPage::$templateName
	 */
	public $templateName = 'roomAdd';
	
	/**
	 * @see	\wcf\acp\form\ACPForm::$activeMenuItem
	 */
	public $activeMenuItem = 'chat.acp.menu.link.room.list';
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.chat.canEditRoom');
	
	/**
	 * room id
	 * 
	 * @var	integer
	 */
	public $roomID = 0;
	
	/**
	 * room object
	 * 
	 * @var	\chat\data\room\Room
	 */
	public $roomObj = null;
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) $this->roomID = intval($_REQUEST['id']);
		$this->roomObj = new \chat\data\room\Room($this->roomID);
		if (!$this->roomObj->roomID) {
			throw new \wcf\system\exception\IllegalLinkException();
		}
		if (!$this->roomObj->permanent) {
			throw new \wcf\system\exception\PermissionDeniedException();
		}
	}
	
	/**
	 * @see	\wcf\form\IForm::save()
	 */
	public function save() {
		\wcf\form\AbstractForm::save();
		
		$this->title = 'chat.room.title'.$this->roomObj->roomID;
		if (I18nHandler::getInstance()->isPlainValue('title')) {
			I18nHandler::getInstance()->remove($this->title);
			$this->title = I18nHandler::getInstance()->getValue('title');
		}
		else {
			I18nHandler::getInstance()->save('title', $this->title, 'chat.room', \chat\util\ChatUtil::getPackageID());
		}
		
		$this->topic = 'chat.room.topic'.$this->roomObj->roomID;
		if (I18nHandler::getInstance()->isPlainValue('topic')) {
			I18nHandler::getInstance()->remove($this->topic);
			$this->topic = I18nHandler::getInstance()->getValue('topic');
		}
		else {
			I18nHandler::getInstance()->save('topic', $this->topic, 'chat.room', \chat\util\ChatUtil::getPackageID());
		}
		
		\wcf\system\acl\ACLHandler::getInstance()->save($this->roomID, $this->objectTypeID);
		\wcf\system\acl\ACLHandler::getInstance()->disableAssignVariables(); 
		\chat\system\permission\PermissionHandler::clearCache();
		
		// update room
		$this->objectAction = new \chat\data\room\RoomAction(array($this->roomID), 'update', array('data' => array_merge($this->additionalFields, array(
			'title' => $this->title,
			'topic' => $this->topic
		))));
		$this->objectAction->executeAction();
		
		$this->saved();
		
		// show success
		WCF::getTPL()->assign(array(
			'success' => true
		));
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		if (!count($_POST)) {
			I18nHandler::getInstance()->setOptions('title', \chat\util\ChatUtil::getPackageID(), $this->roomObj->title, 'chat.room.title\d+');
			I18nHandler::getInstance()->setOptions('topic', \chat\util\ChatUtil::getPackageID(), $this->roomObj->topic, 'chat.room.topic\d+');
			
			$this->title = $this->roomObj->title;
			$this->topic = $this->roomObj->topic;
		}
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		I18nHandler::getInstance()->assignVariables((bool) count($_POST));
		
		WCF::getTPL()->assign(array(
			'roomID' => $this->roomID,
			'action' => 'edit'
		));
	}
}
