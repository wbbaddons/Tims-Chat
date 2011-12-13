<?php
namespace wcf\form;
use \wcf\data\chat;
use \wcf\system\exception\PermissionDeniedException;
use \wcf\system\exception\UserInputException;
use \wcf\system\package\PackageDependencyHandler;
use \wcf\system\user\storage\UserStorageHandler;
use \wcf\system\WCF;
use \wcf\util\StringUtil;

/**
 * Inserts a message
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2011 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	timwolla.wcf.chat
 * @subpackage	form
 */
class ChatForm extends AbstractForm {
	public $message = '';
	public $userData = array();
	public $useTemplate = false;
	
	/**
	 * @see	\wcf\page\AbstractPage::readData()
	 */
	public function readData() {
		$this->readUserData();
		parent::readData();
	}
	
	/**
	 * @see	\wcf\form\AbstractForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_REQUEST['text'])) $this->message = StringUtil::trim($_REQUEST['text']);
	}
	
	/**
	 * Reads user data.
	 */
	public function readUserData() {
		// TODO: Move this into ChatUtil
		$ush = UserStorageHandler::getInstance();
		$packageID = PackageDependencyHandler::getPackageID('timwolla.wcf.chat');
		
		// load storage
		$ush->loadStorage(array(WCF::getUser()->userID), $packageID);
		$data = $ush->getStorage(array(WCF::getUser()->userID), 'color', $packageID);
		
		if ($data[WCF::getUser()->userID] === null) {
			// set defaults
			$data[WCF::getUser()->userID] = array(1 => 0xFF0000, 2 => 0x00FF00); // TODO: Change default values
			$ush->update(WCF::getUser()->userID, 'color', serialize($data[WCF::getUser()->userID]), $packageID);
		}
		else {
			// load existing data
			$data[WCF::getUser()->userID] = unserialize($data[WCF::getUser()->userID]);
		}
		
		$this->userData['color'] = $data[WCF::getUser()->userID];
		
		$data = $ush->getStorage(array(WCF::getUser()->userID), 'roomID', $packageID);
		$this->userData['roomID'] = $data[WCF::getUser()->userID];
	}
	
	/**
	 * @see	\wcf\form\AbstractForm::validate()
	 */
	public function validate() {
		parent::validate();
		if ($this->message === '') {
			throw new UserInputException('text');
		}
		if ($this->userData['roomID'] === null) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * @see	\wcf\form\AbstractForm::save()
	 */
	public function save() {
		parent::save();
		
		$commandHandler = new \wcf\system\chat\commands\ChatCommandHandler();
		var_dump($commandHandler->isCommand($this->message));
		$messageAction = new chat\message\ChatMessageAction(array(), 'create', array(
			'data' => array(
				'roomID' => $this->userData['roomID'],
				'sender' => WCF::getUser()->userID,
				'username' => WCF::getUser()->username,
				'time' => TIME_NOW,
				'type' => chat\message\ChatMessage::TYPE_NORMAL,
				'message' => $this->message,
				'color1' => $this->userData['color'][1],
				'color2' => $this->userData['color'][2]
			)
		));
		$messageAction->executeAction();
		
		$this->saved();
	}
}
