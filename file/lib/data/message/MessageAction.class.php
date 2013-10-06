<?php
namespace chat\data\message;
use chat\data\room;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\MessageUtil;

/**
 * Executes message related actions.
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2013 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	chat.message
 */
class MessageAction extends \wcf\data\AbstractDatabaseObjectAction {
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = '\chat\data\message\MessageEditor';
	
	/**
	 * Removes old messages.
	 * 
	 * @return	integer	Number of deleted messages.
	 */
	public function prune() {
		if (CHAT_LOG_ARCHIVETIME == -1) return 0;
		
		$sql = "SELECT
				".call_user_func(array($this->className, 'getDatabaseTableIndexName'))."
			FROM
				".call_user_func(array($this->className, 'getDatabaseTableName'))."
			WHERE
				time < ?";
		$stmt = \wcf\system\WCF::getDB()->prepareStatement($sql);
		$stmt->execute(array(TIME_NOW - CHAT_LOG_ARCHIVETIME * 60));
		
		$objectIDs = array();
		while ($objectID = $stmt->fetchColumn()) $objectIDs[] = $objectID;
		
		return call_user_func(array($this->className, 'deleteAll'), $objectIDs);
	}
	
	/**
	 * Validates message sending.
	 */
	public function validateSend() {
		// read user data
		$this->parameters['userData']['color1'] = WCF::getUser()->chatColor1;
		$this->parameters['userData']['color2'] = WCF::getUser()->chatColor2;
		$this->parameters['userData']['roomID'] = WCF::getUser()->chatRoomID;
		$this->parameters['userData']['away'] = WCF::getUser()->chatAway;
		
		// read and validate room
		$this->parameters['room'] = room\RoomCache::getInstance()->getRoom($this->parameters['userData']['roomID']);
		if ($this->parameters['room'] === null) throw new \wcf\system\exception\IllegalLinkException();
		if (!$this->parameters['room']->canEnter()) throw new \wcf\system\exception\PermissionDeniedException();
		if (!$this->parameters['room']->canWrite()) throw new \wcf\system\exception\PermissionDeniedException();
		
		// read parameters
		$this->readString('text');
		$this->readBoolean('enableSmilies');
		$this->parameters['text'] = MessageUtil::stripCrap($this->parameters['text']);
		$this->parameters['enableHTML'] = false;
		
		// validate text
		if (mb_strlen($this->parameters['text']) > CHAT_MAX_LENGTH) throw new UserInputException('text', 'tooLong');
		
		// search for disallowed bbcodes
		$disallowedBBCodes = \wcf\system\bbcode\BBCodeParser::getInstance()->validateBBCodes($this->parameters['text'], explode(',', WCF::getSession()->getPermission('user.chat.allowedBBCodes')));
		if (!empty($disallowedBBCodes)) {
			throw new UserInputException('text', WCF::getLanguage()->getDynamicVariable('wcf.message.error.disallowedBBCodes', array('disallowedBBCodes' => $disallowedBBCodes)));
		}
		
		// search for censored words
		if (ENABLE_CENSORSHIP) {
			$result = \wcf\system\message\censorship\Censorship::getInstance()->test($this->parameters['text']);
			if ($result) {
				throw new UserInputException('message', WCF::getLanguage()->getDynamicVariable('wcf.message.error.censoredWordsFound', array('censoredWords' => $result)));
			}
		}
		
		$editor = new \wcf\data\user\UserEditor(WCF::getUser());
		$editor->update(array(
			'chatAway' => null,
			'chatLastActivity' => TIME_NOW
		));
		
		// mark user as back
		if ($this->parameters['userData']['away'] !== null) {
			$messageAction = new MessageAction(array(), 'create', array(
				'data' => array(
					'roomID' => $this->parameters['room']->roomID,
					'sender' => WCF::getUser()->userID,
					'username' => WCF::getUser()->username,
					'time' => TIME_NOW,
					'type' => Message::TYPE_BACK,
					'message' => '',
					'color1' => $this->parameters['userData']['color1'],
					'color2' => $this->parameters['userData']['color2']
				)
			));
			$messageAction->executeAction();
		}
		
		// handle commands
		$commandHandler = new \chat\system\command\CommandHandler($this->parameters['text'], $this->parameters['room']);
		if ($commandHandler->isCommand()) {
			try {
				$command = $commandHandler->loadCommand();
				
				if ($command->enableSmilies != \chat\system\command\ICommand::SETTING_USER) $this->parameters['enableSmilies'] = $command->enableSmilies;
				$this->parameters['enableHTML'] = $command->enableHTML;
				
				$this->parameters['type'] = $command->getType();
				$this->parameters['text'] = $command->getMessage();
				$this->parameters['receiver'] = $command->getReceiver();
				$this->parameters['additionalData'] = $command->getAdditionalData();
			}
			catch (\chat\system\command\NotFoundException $e) {
				throw new UserInputException('text', WCF::getLanguage()->getDynamicVariable('chat.error.notFound', array('exception' => $e)));
			}
			catch (\chat\system\command\UserNotFoundException $e) {
				throw new UserInputException('text', WCF::getLanguage()->getDynamicVariable('chat.error.userNotFound', array('exception' => $e)));
			}
			catch (\InvalidArgumentException $e) {
				throw new UserInputException('text', WCF::getLanguage()->getDynamicVariable('chat.error.invalidArgument', array('exception' => $e)));
			}
		}
		else {
			$this->parameters['type'] = Message::TYPE_NORMAL;
			$this->parameters['receiver'] = null;
			$this->parameters['additionalData'] = null;
			
			$this->parameters['text'] = \wcf\system\bbcode\PreParser::getInstance()->parse($this->parameters['text'], explode(',', WCF::getSession()->getPermission('user.chat.allowedBBCodes')));
		}
	}
	
	/**
	 * Creates sent message.
	 */
	public function send() {
		$this->objectAction = new MessageAction(array(), 'create', array(
			'data' => array(
				'roomID' => $this->parameters['room']->roomID,
				'sender' => WCF::getUser()->userID,
				'username' => WCF::getUser()->username,
				'receiver' => $this->parameters['receiver'],
				'time' => TIME_NOW,
				'type' => $this->parameters['type'],
				'message' => $this->parameters['text'],
				'enableSmilies' => $this->parameters['enableSmilies'] ? 1 : 0,
				'enableHTML' => $this->parameters['enableHTML'] ? 1 : 0,
				'color1' => $this->parameters['userData']['color1'],
				'color2' => $this->parameters['userData']['color2'],
				'additionalData' => serialize($this->parameters['additionalData'])
			)
		));
		$this->objectAction->executeAction();
		$returnValues = $this->objectAction->getReturnValues();
		
		// add activity points
		\wcf\system\user\activity\point\UserActivityPointHandler::getInstance()->fireEvent('be.bastelstu.chat.activityPointEvent.message', $returnValues['returnValues']->messageID, WCF::getUser()->userID);
	}
	
	/**
	 * Fetches messages in between the specified timestamps.
	 *
	 * @return array Array containing message table, containerID and information about an empty message table.
	 */
	public function getMessages() {
		// read messages
		$messages = ViewableMessageList::getMessagesBetween($this->parameters['room'], $this->parameters['start'], $this->parameters['end']);
		
		return array(
			'noMessages' => (count($messages) == 0) ? true : null,
			'containerID' => $this->parameters['containerID'],
			'template' => WCF::getTPL()->fetch('__messageLogTable', 'chat', array('messages' => $messages), true)
		);
	}
	
	/**
	 * Validates getting messages.
	 */
	public function validateGetMessages() {
		// check permissions
		WCF::getSession()->checkPermissions(array('admin.chat.canReadLog'));
		
		// read parameters
		$this->readInteger('roomID');
		$this->readInteger('start');
		$this->readInteger('end');
		
		// read and validate room
		$this->parameters['room'] = room\RoomCache::getInstance()->getRoom($this->parameters['roomID']);
		if ($this->parameters['room'] === null) throw new \wcf\system\exception\IllegalLinkException();
	}
	
	public function validateSendAttachment() {
		// read user data
		$this->parameters['userData']['color1'] = WCF::getUser()->chatColor1;
		$this->parameters['userData']['color2'] = WCF::getUser()->chatColor2;
		$this->parameters['userData']['roomID'] = WCF::getUser()->chatRoomID;
		$this->parameters['userData']['away'] = WCF::getUser()->chatAway;
		
		// read and validate room
		$this->parameters['room'] = room\RoomCache::getInstance()->getRoom($this->parameters['userData']['roomID']);
		if ($this->parameters['room'] === null) throw new \wcf\system\exception\IllegalLinkException();
		if (!$this->parameters['room']->canEnter()) throw new \wcf\system\exception\PermissionDeniedException();
		if (!$this->parameters['room']->canWrite()) throw new \wcf\system\exception\PermissionDeniedException();
		
		$this->readInteger('objectID');
		if (!$this->parameters['objectID']) throw new \wcf\system\exception\IllegalLinkException();
		
		$this->readInteger('parentObjectID');
		if (!$this->parameters['parentObjectID']) throw new \wcf\system\exception\IllegalLinkException();
		
		$editor = new \wcf\data\user\UserEditor(WCF::getUser());
		$editor->update(array(
			'chatAway' => null,
			'chatLastActivity' => TIME_NOW
		));
		
		// mark user as back
		if ($this->parameters['userData']['away'] !== null) {
			$messageAction = new MessageAction(array(), 'create', array(
				'data' => array(
					'roomID' => $this->parameters['room']->roomID,
					'sender' => WCF::getUser()->userID,
					'username' => WCF::getUser()->username,
					'time' => TIME_NOW,
					'type' => Message::TYPE_BACK,
					'message' => '',
					'color1' => $this->parameters['userData']['color1'],
					'color2' => $this->parameters['userData']['color2']
				)
			));
			$messageAction->executeAction();
		}
	}
	
	public function sendAttachment() {
		$this->objectAction = new MessageAction(array(), 'create', array(
			'data' => array(
				'roomID' => $this->parameters['room']->roomID,
				'sender' => WCF::getUser()->userID,
				'username' => WCF::getUser()->username,
				'receiver' => null,
				'time' => TIME_NOW,
				'type' => Message::TYPE_ATTACHMENT,
				'message' => $this->parameters['objectID'],
				'enableSmilies' => 0,
				'enableHTML' => 0,
				'color1' => $this->parameters['userData']['color1'],
				'color2' => $this->parameters['userData']['color2'],
				'additionalData' => null
			)
		));
		
		$this->objectAction->executeAction();
		$returnValues = $this->objectAction->getReturnValues();
		
		$this->parameters['parentObjectID'] = 0;
		$attachmentHandler = new \wcf\system\attachment\AttachmentHandler('be.bastelstu.chat.message', $this->parameters['objectID'], $this->parameters['tmpHash'], $this->parameters['parentObjectID']);
		$attachmentHandler->updateObjectID($returnValues['returnValues']->messageID);
		
		// add activity points
		\wcf\system\user\activity\point\UserActivityPointHandler::getInstance()->fireEvent('be.bastelstu.chat.activityPointEvent.message', $returnValues['returnValues']->messageID, WCF::getUser()->userID);
	}
}
