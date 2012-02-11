<?php
namespace wcf\acp\form;
use wcf\system\language\I18nHandler;
use wcf\system\package\PackageDependencyHandler;
use wcf\system\WCF;

/**
 * Shows the chatroom edit form.
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2012 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	timwolla.wcf.chat
 * @subpackage	acp.form
 */
class ChatRoomEditForm extends ChatRoomAddForm {
	/**
	 * @see \wcf\page\AbstractPage::$templateName
	 */
	public $templateName = 'chatRoomAdd';
	
	/**
	 * @see \wcf\acp\form\ACPForm::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.chat.room.list';
	
	/**
	 * @see \wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.content.chat.canEditRoom');
	
	/**
	 * room id
	 * 
	 * @var	integer
	 */
	public $roomID = 0;
	
	/**
	 * room object
	 * 
	 * @var	\wcf\data\chat\room\ChatRoom
	 */
	public $roomObj = null;
	
	/**
	 * @see wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) $this->roomID = intval($_REQUEST['id']);
		$this->roomObj = new \wcf\data\chat\room\ChatRoom($this->roomID);
		if (!$this->roomObj->roomID) {
			throw new \wcf\system\exception\IllegalLinkException();
		}
	}
	
	/**
	 * @see wcf\form\IForm::save()
	 */
	public function save() {
		ACPForm::save();
		
		$this->title = 'wcf.chat.room.title'.$this->roomObj->roomID;
		if (I18nHandler::getInstance()->isPlainValue('title')) {
			I18nHandler::getInstance()->remove($this->title, PackageDependencyHandler::getPackageID('timwolla.wcf.chat'));
			$this->title = I18nHandler::getInstance()->getValue('title');
		}
		else {
			I18nHandler::getInstance()->save('title', $this->title, 'wcf.chat.room', PackageDependencyHandler::getPackageID('timwolla.wcf.chat'));
		}
		
		$this->topic = 'wcf.chat.room.topic'.$this->roomObj->roomID;
		if (I18nHandler::getInstance()->isPlainValue('topic')) {
			I18nHandler::getInstance()->remove($this->topic, PackageDependencyHandler::getPackageID('timwolla.wcf.chat'));
			$this->topic = I18nHandler::getInstance()->getValue('topic');
		}
		else {
			I18nHandler::getInstance()->save('topic', $this->topic, 'wcf.chat.room', PackageDependencyHandler::getPackageID('timwolla.wcf.chat'));
		}
		
		
		// update room
		$this->objectAction = new \wcf\data\chat\room\ChatRoomAction(array($this->roomID), 'update', array('data' => array(
			'title' => $this->title,
			'topic' => $this->topic
		)));
		$this->objectAction->executeAction();
		
		$this->saved();
		
		// show success
		WCF::getTPL()->assign(array(
			'success' => true
		));
	}
	
	/**
	 * @see wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		if (!count($_POST)) {
			I18nHandler::getInstance()->setOptions('title', PackageDependencyHandler::getPackageID('timwolla.wcf.chat'), $this->roomObj->title, 'wcf.chat.room.title\d+');
			I18nHandler::getInstance()->setOptions('topic', PackageDependencyHandler::getPackageID('timwolla.wcf.chat'), $this->roomObj->topic, 'wcf.chat.room.topic\d+');
			
			$this->title = $this->roomObj->title;
			$this->topic = $this->roomObj->topic;
		}
	}
	
	/**
	 * @see wcf\page\IPage::assignVariables()
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