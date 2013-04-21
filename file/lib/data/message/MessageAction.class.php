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
	 * @return	integer			Number of deleted messages.
	 */
	public function prune() {
		$sql = "SELECT
				".call_user_func(array($this->className, 'getDatabaseTableIndexName'))."
			FROM
				".call_user_func(array($this->className, 'getDatabaseTableName'))."
			WHERE
				time < ?";
		$stmt = \wcf\system\WCF::getDB()->prepareStatement($sql);
		$stmt->execute(array(TIME_NOW - CHAT_LOG_ARCHIVETIME));
		$objectIDs = array();
		while ($objectIDs[] = $stmt->fetchColumn());
		
		return call_user_func(array($this->className, 'deleteAll'), $objectIDs);
	}
	
	/**
	 * Validates message sending.
	 */
	public function validateSend() {
		// read user data
		$this->parameters['userData']['color'] = \chat\util\ChatUtil::readUserData('color');
		$this->parameters['userData']['roomID'] = \chat\util\ChatUtil::readUserData('roomID');
		$this->parameters['userData']['away'] = \chat\util\ChatUtil::readUserData('away');
		
		// read and validate room
		$cache = room\Room::getCache();
		if (!isset($cache[$this->parameters['userData']['roomID']])) throw new \wcf\system\exception\IllegalLinkException();
		$this->parameters['room'] = $cache[$this->parameters['userData']['roomID']];
		
		if (!$this->parameters['room']->canEnter() || !$this->parameters['room']->canWrite()) throw new \wcf\system\exception\PermissionDeniedException();
		
		// read parameters
		$this->readString('text');
		$this->readBoolean('enableSmilies');
		$this->parameters['text'] = MessageUtil::stripCrap($this->parameters['text']);
		$this->parameters['enableHTML'] = false;
		
		// validate text
		if (strlen($this->parameters['text']) > CHAT_MAX_LENGTH) throw new UserInputException('text', 'tooLong');
		
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
		
		\chat\util\ChatUtil::writeUserData(array('away' => null));
		
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
					'color1' => $this->parameters['userData']['color'][1],
					'color2' => $this->parameters['userData']['color'][2]
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
			}
			catch (\chat\system\command\NotFoundException $e) {
				throw new UserInputException('text', WCF::getLanguage()->getDynamicVariable('chat.error.notFound', array('exception' => $e)));
			}
			catch (\chat\system\command\UserNotFoundException $e) {
				throw new UserInputException('text', WCF::getLanguage()->getDynamicVariable('chat.error.userNotFound', array('exception' => $e)));
			}
		}
		else {
			$this->parameters['type'] = Message::TYPE_NORMAL;
			$this->parameters['receiver'] = null;
			
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
				'color1' => $this->parameters['userData']['color'][1],
				'color2' => $this->parameters['userData']['color'][2]
			)
		));
		$this->objectAction->executeAction();
		$returnValues = $this->objectAction->getReturnValues();
		
		// add activity points
		\wcf\system\user\activity\point\UserActivityPointHandler::getInstance()->fireEvent('be.bastelstu.chat.activityPointEvent.message', $returnValues['returnValues']->messageID, WCF::getUser()->userID);
	}
}
