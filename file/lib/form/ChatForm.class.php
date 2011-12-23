<?php
namespace wcf\form;
use \wcf\data\chat;
use \wcf\system\exception\PermissionDeniedException;
use \wcf\system\exception\UserInputException;
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
	public $enableSmilies = 1;
	public $userData = array();
	public $useTemplate = false;
	
	/**
	 * @see	\wcf\page\AbstractPage::readData()
	 */
	public function readData() {
		$this->userData['color'] = \wcf\util\ChatUtil::readUserData('color');
		$this->userData['roomID'] = \wcf\util\ChatUtil::readUserData('roomID');
		
		parent::readData();
	}
	
	/**
	 * @see	\wcf\form\AbstractForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_REQUEST['text'])) $this->message = StringUtil::trim($_REQUEST['text']);
		if (isset($_REQUEST['smilies'])) $this->enableSmilies = intval($_REQUEST['smilies']);
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
				'enableSmilies' => $this->enableSmilies,
				'color1' => $this->userData['color'][1],
				'color2' => $this->userData['color'][2]
			)
		));
		$messageAction->executeAction();
		
		$this->saved();
	}
}
