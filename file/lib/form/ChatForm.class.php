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
	/**
	 * Should HTML be enabled for this message.
	 * 
	 * @var integer
	 */
	public $enableHTML = 0;
	
	/**
	 * Should bbcodes be enabled for this message.
	 *
	 * @var integer
	 */
	public $enableBBCodes = CHAT_ENABLE_BBCODES;
	
	/**
	 * Should smilies be enabled for this message.
	 * 
	 * @var integer
	 */
	public $enableSmilies = 1;
	
	/**
	 * @see wcf\page\AbstractPage::$loginRequired
	 */
	public $loginRequired = true;
	
	/**
	 * @see \wcf\page\AbstractPage::$neededModules
	 */
	public $neededModules = array('CHAT_ACTIVE');
	
	/**
	 * @see \wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array();
	
	/**
	 * The given message-string.
	 * 
	 * @var string
	 */
	public $message = '';
	
	/**
	 * The current room.
	 * 
	 * @var \wcf\data\chat\room\ChatRoom
	 */
	public $room = null;
	
	/**
	 * Values read from the UserStorage of the current user.
	 * 
	 * @var array
	 */
	public $userData = array();
	
	/**
	 * @see	\wcf\page\AbstractForm::$useTemplate
	 */
	public $useTemplate = false;
	
	/**
	 * shortcut for the active request
	 * @see wcf\system\request\Request::getRequestObject()
	 */
	public $request = null;
	
	/**
	 * Disallows direct access.
	 * 
	 * @see wcf\page\IPage::__run()
	 */
	public function __run() {
		if (($this->request = \wcf\system\request\RequestHandler::getInstance()->getActiveRequest()->getRequestObject()) === $this) throw new \wcf\system\exception\IllegalLinkException();
		
		parent::__run();
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		$this->userData['color'] = \wcf\util\ChatUtil::readUserData('color');
		$this->userData['roomID'] = \wcf\util\ChatUtil::readUserData('roomID');
		$this->userData['away'] = \wcf\util\ChatUtil::readUserData('away');
		
		$cache = chat\room\ChatRoom::getCache();
		if (!isset($cache[$this->userData['roomID']])) throw new \wcf\system\exception\IllegalLinkException();
		$this->room = $cache[$this->userData['roomID']];
		
		if (!$this->room->canEnter()) throw new \wcf\system\exception\PermissionDeniedException();
		if (!$this->room->canWrite()) throw new \wcf\system\exception\PermissionDeniedException();
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
		$commandHandler = new \wcf\system\chat\command\CommandHandler($this->message);
		if ($commandHandler->isCommand()) {
			try {
				$command = $commandHandler->loadCommand();
				
				if ($command->enableSmilies != \wcf\system\chat\command\ICommand::SETTING_USER) $this->enableSmilies = $command->enableSmilies;
				$this->enableHTML = $command->enableHTML;
				if ($command->enableBBCodes != \wcf\system\chat\command\ICommand::SETTING_USER) $this->enableBBCodes = $command->enableBBCodes;
				
				$type = $command->getType();
				$this->message = $command->getMessage();
				$receiver = $command->getReceiver();
			}
			catch (\wcf\system\chat\command\NotFoundException $e) {
				$this->message = WCF::getLanguage()->get('wcf.chat.error.notFound');
				$type = chat\message\ChatMessage::TYPE_ERROR;
				$receiver = WCF::getUser()->userID;
			}
			catch (\wcf\system\chat\command\UserNotFoundException $e) {
				$this->message = WCF::getLanguage()->getDynamicVariable('wcf.chat.error.userNotFound', array('username' => $e->getUsername()));
				$type = chat\message\ChatMessage::TYPE_ERROR;
				$receiver = WCF::getUser()->userID;
				$this->enableHTML = 1;
			}
			catch (\wcf\system\exception\PermissionDeniedException $e) {
				$this->message = WCF::getLanguage()->get('wcf.chat.error.permissionDenied');
				$type = chat\message\ChatMessage::TYPE_ERROR;
				$receiver = WCF::getUser()->userID;
			}
			catch (\Exception $e) {
				$this->message = WCF::getLanguage()->get('wcf.chat.error.exception');
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
		
		$this->objectAction = new chat\message\ChatMessageAction(array(), 'create', array(
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
				'enableBBCodes' => $this->enableBBCodes,
				'color1' => $this->userData['color'][1],
				'color2' => $this->userData['color'][2]
			)
		));
		$this->objectAction->executeAction();
		
		// add activity points
		\wcf\system\user\activity\point\UserActivityPointHandler::getInstance()->fireEvent('be.bastelstu.wcf.chat.activityPointEvent.message', TIME_NOW, WCF::getUser()->userID);
		
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
