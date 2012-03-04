<?php
namespace wcf\form;
use \wcf\data\chat;
use \wcf\system\exception\UserInputException;
use \wcf\system\WCF;
use \wcf\util\StringUtil;

/**
 * Inserts a message
 * 
 * @author	Tim Düsterhus
 * @copyright	2010-2012 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	timwolla.wcf.chat
 * @subpackage	form
 */
class ChatForm extends AbstractForm {
	public $enableSmilies = 1;
	public $message = '';
	public $room = null;
	public $userData = array();
	
	/**
	 * @see	\wcf\page\AbstractForm::$useTemplate
	 */
	public $useTemplate = false;
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		$this->userData['color'] = \wcf\util\ChatUtil::readUserData('color');
		$this->userData['roomID'] = \wcf\util\ChatUtil::readUserData('roomID');
		
		$this->room = chat\room\ChatRoom::getCache()->search($this->userData['roomID']);
		if (!$this->room) throw new \wcf\system\exception\IllegalLinkException();
		if (!$this->room->canEnter()) throw new \wcf\system\exception\PermissionDeniedException();
		
		parent::readData();
	}
	
	/**
	 * @see	\wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_REQUEST['text'])) $this->message = StringUtil::trim($_REQUEST['text']);
		if (isset($_REQUEST['smilies'])) $this->enableSmilies = intval($_REQUEST['smilies']);
	}
	
	/**
	 * @see	\wcf\form\IForm::validate()
	 */
	public function validate() {
		parent::validate();
		
		if ($this->message === '') {
			throw new UserInputException('text');
		}
		
		if (strlen($this->message) > CHAT_MAX_LENGTH) {
			throw new UserInputException('text', 'tooLong');
		}
	}
	
	/**
	 * @see	\wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();
		
		$commandHandler = new \wcf\system\chat\commands\ChatCommandHandler();
		$messageAction = new chat\message\ChatMessageAction(array(), 'create', array(
			'data' => array(
				'roomID' => $this->room->roomID,
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
	
	/**
	 * @see	\wcf\page\IPage::show()
	 */
	public function show() {
		header("HTTP/1.0 204 No Content");
		parent::show();
	}
}
