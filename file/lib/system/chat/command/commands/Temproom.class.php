<?php
namespace wcf\system\chat\command\commands;
use \wcf\system\WCF;
use \wcf\util\StringUtil;

/**
 * Creates a temporary room
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2012 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.wcf.chat
 * @subpackage	system.chat.command.commands
 */
class Temproom extends \wcf\system\chat\command\AbstractRestrictedCommand {
	public $roomName = '';
	
	public function __construct(\wcf\system\chat\command\CommandHandler $commandHandler) {
		parent::__construct($commandHandler);
		
		// create room
		$this->objectAction = new \wcf\data\chat\room\ChatRoomAction(array(), 'create', array('data' => array(
			'title' => 'Temproom',
			'topic' => '',
			'permanent' => 0,
			'owner' => WCF::getUser()->userID
		)));
		$this->objectAction->executeAction();
		$returnValues = $this->objectAction->getReturnValues();
		$chatRoomEditor = new \wcf\data\chat\room\ChatRoomEditor($returnValues['returnValues']);
		$roomID = $returnValues['returnValues']->roomID;
		$this->roomName = WCF::getLanguage()->getDynamicVariable('wcf.chat.room.titleTemp', array('roomID' => $roomID));
		
		// set accurate title
		$chatRoomEditor->update(array(
			'title' => $this->roomName
		));
		
		// set permissions
		$options = \wcf\data\acl\option\ACLOption::getOptions(\wcf\system\acl\ACLHandler::getInstance()->getObjectTypeID('be.bastelstu.wcf.chat.room'))->getObjects();
		$_POST['aclValues'] = array(
			'user' => array(
				// creators may do everything
				WCF::getUser()->userID => array_fill_keys(array_keys($options), 1)
			),
			'group' => array(
				// anyone else may do nothing
				\wcf\data\user\group\UserGroup::EVERYONE => array_fill_keys(array_keys($options), 0)
			)
		);
		
		\wcf\system\acl\ACLHandler::getInstance()->save($roomID, \wcf\system\acl\ACLHandler::getInstance()->getObjectTypeID('be.bastelstu.wcf.chat.room'));
		\wcf\system\chat\permission\ChatPermissionHandler::clearCache();
		$this->didInit();
	}
	
	/**
	 * @see	\wcf\system\chat\command\AbstractRestrictedCommand::checkPermission()
	 */
	public function checkPermission() {
		parent::checkPermission();
		
		WCF::getSession()->checkPermissions(array('user.chat.canTempRoom'));
	}
	
	/**
	 * @see	\wcf\system\chat\command\ICommand::getType()
	 */
	public function getType() {
		return \wcf\data\chat\message\ChatMessage::TYPE_INFORMATION;
	}
	
	/**
	 * @see	\wcf\system\chat\command\ICommand::getMessage()
	 */
	public function getMessage() {
		return $this->roomName;
	}
	
	/**
	 * @see	\wcf\system\chat\command\ICommand::getReceiver()
	 */
	public function getReceiver() {
		return \wcf\system\WCF::getUser()->userID;
	}
}
