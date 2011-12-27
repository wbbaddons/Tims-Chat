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
	public $enableSmilies = 1;
	public $message = '';
	public $room = null;
	public $userData = array();
	public $useTemplate = false;
	
	/**
	 * @see	\wcf\page\AbstractPage::readData()
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
	}
	
	/**
	 * @see	\wcf\form\AbstractForm::save()
	 */
	public function save() {
		parent::save();
		
		$commandHandler = new \wcf\system\chat\commands\CommandHandler($this->message);
		if ($commandHandler->isCommand()) {
			try {
				$command = $commandHandler->loadCommand();
				
				if ($command::ENABLE_SMILIES != \wcf\system\chat\commands\ICommand::SMILEY_USER) $this->enableSmilies = $command::ENABLE_SMILIES;
				$type = $command->getType();
				$this->message = $command->getMessage();
				$receiver = $command->getReceiver();
			}
			catch (\wcf\system\chat\commands\NotFoundException $e) {
				$this->message = WCF::getLanguage()->get('wcf.chat.command.error.notFound');
				$type = chat\message\ChatMessage::TYPE_ERROR;
				$receiver = WCF::getUser()->userID;
			}
			catch (\wcf\system\exception\PermissionDeniedException $e) {
				$this->message = WCF::getLanguage()->get('wcf.chat.command.error.permissionDenied');
				$type = chat\message\ChatMessage::TYPE_ERROR;
				$receiver = WCF::getUser()->userID;
			}
		}
		else {
			$type = chat\message\ChatMessage::TYPE_NORMAL;
			$receiver = null;
		}
		
		$messageAction = new chat\message\ChatMessageAction(array(), 'create', array(
			'data' => array(
				'roomID' => $this->room->roomID,
				'sender' => WCF::getUser()->userID,
				'username' => WCF::getUser()->username,
				'receiver' => $receiver,
				'time' => TIME_NOW,
				'type' => $type,
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
