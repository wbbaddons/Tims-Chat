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
 * @package	be.bastelstu.wcf.chat
 * @subpackage	form
 */
class ChatForm extends AbstractForm {
	public $enableHTML = 0;
	public $enableSmilies = 1;
	public $neededPermissions = array('user.chat.canEnter');
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
		$this->userData['away'] = \wcf\util\ChatUtil::readUserData('away');
		
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
		
		if (isset($_REQUEST['text'])) $this->message = \wcf\util\MessageUtil::stripCrap(StringUtil::trim($_REQUEST['text']));
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
		
		\wcf\util\ChatUtil::writeUserData(array('away' => null));
		$commandHandler = new \wcf\system\chat\commands\CommandHandler($this->message);
		if ($commandHandler->isCommand()) {
			try {
				$command = $commandHandler->loadCommand();
				
				if ($command->enableSmilies != \wcf\system\chat\commands\ICommand::SMILEY_USER) $this->enableSmilies = $command->enableSmilies;
				$this->enableHTML = $command->enableHTML;
				$type = $command->getType();
				$this->message = $command->getMessage();
				$receiver = $command->getReceiver();
			}
			catch (\wcf\system\chat\commands\NotFoundException $e) {
				$this->message = WCF::getLanguage()->get('wcf.chat.command.error.notFound');
				$type = chat\message\ChatMessage::TYPE_ERROR;
				$receiver = WCF::getUser()->userID;
			}
			catch (\wcf\system\chat\commands\UserNotFoundException $e) {
				$this->message = WCF::getLanguage()->get('wcf.chat.command.error.userNotFound');
				$type = chat\message\ChatMessage::TYPE_ERROR;
				$receiver = WCF::getUser()->userID;
			}
			catch (\wcf\system\exception\PermissionDeniedException $e) {
				$this->message = WCF::getLanguage()->get('wcf.chat.command.error.permissionDenied');
				$type = chat\message\ChatMessage::TYPE_ERROR;
				$receiver = WCF::getUser()->userID;
			}
			catch (\Exception $e) {
				$this->message = WCF::getLanguage()->get('wcf.chat.command.error.exception');
				$type = chat\message\ChatMessage::TYPE_ERROR;
				$receiver = WCF::getUser()->userID;
			}
		}
		else {
			$type = chat\message\ChatMessage::TYPE_NORMAL;
			$receiver = null;
		}
		
		// mark user as back
		if ($this->userData['away'] !== null) {
			$messageAction = new chat\message\ChatMessageAction(array(), 'create', array(
				'data' => array(
					'roomID' => $this->room->roomID,
					'sender' => WCF::getUser()->userID,
					'username' => WCF::getUser()->username,
					'time' => TIME_NOW,
					'type' => chat\message\ChatMessage::TYPE_BACK,
					'message' => '',
					'color1' => $this->userData['color'][1],
					'color2' => $this->userData['color'][2]
				)
			));
			$messageAction->executeAction();
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
				'enableHTML' => $this->enableHTML,
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
