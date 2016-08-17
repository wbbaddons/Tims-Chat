<?php
namespace chat\data\message;
use \chat\util\ChatUtil;
use \wcf\system\bbcode\AttachmentBBCode;
use \wcf\system\Regex;
use \wcf\system\WCF;

/**
 * Represents a chat message.
 * 
 * @author	Tim Düsterhus
 * @copyright	2010-2014 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	data.message
 */
class Message extends \chat\data\CHATDatabaseObject {
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'message';
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'messageID';
	
	const TYPE_NORMAL = 0;
	const TYPE_JOIN = 1;
	const TYPE_LEAVE = 2;
	const TYPE_AWAY = 3;
	const TYPE_BACK = 4;
	const TYPE_MODERATE = 5;
	const TYPE_ME = 6;
	const TYPE_WHISPER = 7;
	const TYPE_INFORMATION = 8;
	const TYPE_CLEAR = 9;
	const TYPE_TEAM = 10;
	const TYPE_GLOBALMESSAGE = 11;
	const TYPE_ATTACHMENT = 12;
	
	/**
	 * cache for users
	 * @var array<\wcf\data\user\User>
	 */
	protected static $users = array();
	
	/**
	 * @see	\wcf\data\DatabaseObject::handleData()
	 */
	protected function handleData($data) {
		parent::handleData($data);
		
		if ($this->data['additionalData'] !== null) {
			$this->data['additionalData'] = unserialize($this->data['additionalData']);
		}
	}
	
	/**
	 * @see	\chat\data\message\Message::getFormattedMessage()
	 */
	public function __toString() {
		return $this->getFormattedMessage();
	}
	
	/**
	 * Returns the formatted message.
	 * 
	 * @param	string	$outputType	outputtype for messageparser
	 * @return	string
	 */
	public function getFormattedMessage($type = 'text/html') {
		$message = $this->message;
		$messageParser = \wcf\system\bbcode\MessageParser::getInstance();
		$messageParser->setOutputType($type);
		
		switch ($this->type) {
			case self::TYPE_JOIN:
			case self::TYPE_LEAVE:
			case self::TYPE_BACK:
				$message = WCF::getLanguage()->getDynamicVariable('chat.message.'.$this->type, $this->data['additionalData'] ?: array());
			break;
			case self::TYPE_AWAY:
				$message = WCF::getLanguage()->getDynamicVariable('chat.message.'.$this->type, array('message' => $message));
			break;
			case self::TYPE_MODERATE:
				$message = unserialize($message);
				$message = WCF::getLanguage()->getDynamicVariable('chat.message.'.$this->type.'.'.$message['type'], $message ?: array());
				
				if ($type === 'text/plain') return $message;
				$message = $messageParser->parse($message, false, false, true, false);
			break;
			case self::TYPE_ATTACHMENT:
				$attachmentList = new \wcf\data\attachment\GroupedAttachmentList('be.bastelstu.chat.message');
				$attachmentList->getConditionBuilder()->add('attachment.objectID IN (?)', array($this->messageID));
				$attachmentList->readObjects();
				
				AttachmentBBCode::setAttachmentList($attachmentList);
				AttachmentBBCode::setObjectID($this->messageID);
				
				$message = $messageParser->parse($message, false, false, true, false);
			break;
			case self::TYPE_WHISPER:
			case self::TYPE_NORMAL:
			case self::TYPE_ME:
			default:
				if ($type === 'text/plain') return $message;
				
				$message = $messageParser->parse($message, $this->enableSmilies, $this->enableHTML, true, false);
			break;
		}
		
		return $message;
	}
	
	/**
	 * Returns the username.
	 * 
	 * @param	boolean		$colored
	 * @return	string
	 */
	public function getUsername($colored = false) {
		$username = $this->username;
		if ($this->type == self::TYPE_INFORMATION) return WCF::getLanguage()->get('chat.global.information');
		
		if ($colored) {
			$username = \chat\util\ChatUtil::gradient($username, $this->color1, $this->color2);
		}
		
		return $username;
	}
	
	/**
	 * Converts this message into json-form.
	 * 
	 * @param	boolean	$raw
	 * @return	string
	 */
	public function jsonify($raw = false) {
		$additionalData = $this->additionalData;
		switch ($this->type) {
			case self::TYPE_WHISPER:
			case self::TYPE_NORMAL:
			case self::TYPE_INFORMATION:
				$separator = ':';
			break;
			case self::TYPE_JOIN:
				unset($additionalData['ipAddress']);
			break;
			default:
				$separator = '';
			break;
		}
		
		$array = array(
			'formattedUsername' => $this->getUsername(true),
			'formattedMessage' => $this->getFormattedMessage('text/html'),
			'formattedTime' => \wcf\util\DateUtil::format(\wcf\util\DateUtil::getDateTimeByTimestamp($this->time), 'H:i:s'),
			'separator' => $separator,
			'message' => $this->getFormattedMessage('text/plain'),
			'sender' => (int) $this->sender,
			'username' => $this->getUsername(),
			'time' => (int) $this->time,
			'receiver' => (int) $this->receiver,
			'type' => (int) $this->type,
			'roomID' => (int) $this->roomID,
			'messageID' => (int) $this->messageID,
			'additionalData' => $additionalData
		);
		
		if ($raw) return $array;
		return \wcf\util\JSON::encode($array);
	}
}
