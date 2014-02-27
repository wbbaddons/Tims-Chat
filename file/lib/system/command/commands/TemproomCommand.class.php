<?php
namespace chat\system\command\commands;
use \wcf\system\WCF;

/**
 * Creates a temporary room
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2014 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	system.chat.command.commands
 */
class TemproomCommand extends \chat\system\command\AbstractRestrictedCommand {
	public $roomName = '';
	public $roomID = 0;
	
	public function __construct(\chat\system\command\CommandHandler $commandHandler) {
		parent::__construct($commandHandler);
		
		// create room
		$this->objectAction = new \chat\data\room\RoomAction(array(), 'create', array('data' => array(
			'title' => 'Temproom',
			'topic' => '',
			'permanent' => 0,
			'owner' => WCF::getUser()->userID
		)));
		$this->objectAction->executeAction();
		$returnValues = $this->objectAction->getReturnValues();
		$chatRoomEditor = new \chat\data\room\RoomEditor($returnValues['returnValues']);
		$this->roomID = $returnValues['returnValues']->roomID;
		$this->roomName = WCF::getLanguage()->getDynamicVariable('chat.room.titleTemp', array('roomID' => $this->roomID));
		
		// set accurate title
		$chatRoomEditor->update(array(
			'title' => $this->roomName
		));
		
		// set permissions
		$acl = \wcf\system\acl\ACLHandler::getInstance();
		$options = $acl->getOptions($acl->getObjectTypeID('be.bastelstu.chat.room'))->getObjects();
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
		
		$acl->save($this->roomID, $acl->getObjectTypeID('be.bastelstu.chat.room'));
		\chat\system\permission\PermissionHandler::clearCache();
		$this->didInit();
	}
	
	/**
	 * @see	\chat\system\command\IRestrictedCommand::checkPermission()
	 */
	public function checkPermission() {
		parent::checkPermission();
		
		WCF::getSession()->checkPermissions(array('user.chat.canTempRoom'));
	}
	
	/**
	 * @see	\chat\system\command\ICommand::getType()
	 */
	public function getType() {
		return \chat\data\message\Message::TYPE_INFORMATION;
	}
	
	/**
	 * @see	\chat\system\command\ICommand::getMessage()
	 */
	public function getMessage() {
		return WCF::getLanguage()->getDynamicVariable('chat.message.temproom.success', array('roomName' => $this->roomName));
	}
	
	/**
	 * @see	\chat\system\command\ICommand::getReceiver()
	 */
	public function getReceiver() {
		return \wcf\system\WCF::getUser()->userID;
	}
	
	/**
	 * @see	\chat\system\command\ICommand::getAdditionalData()
	 */
	public function getAdditionalData() {
		return array(
			'roomID' => (int) $this->roomID,
			'roomName' => $this->roomName
		);
	}
}
