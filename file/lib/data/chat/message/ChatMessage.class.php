<?php
namespace wcf\data\chat\message;
use \wcf\system\WCF;

/**
 * Represents a chat message.
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2012 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.wcf.chat
 * @subpackage	data.chat.message
 */
class ChatMessage extends \wcf\data\DatabaseObject {
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'chat_message';
	
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
	const TYPE_ERROR = 12;
	
	/**
	 * @see	\wcf\data\chat\message\ChatMessage::getFormattedMessage()
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
	public function getFormattedMessage($outputType = 'text/html') {
		$message = $this->message;
		switch ($this->type) {
			case self::TYPE_JOIN:
			case self::TYPE_LEAVE:
			case self::TYPE_BACK:
			case self::TYPE_AWAY:
				WCF::getTPL()->assign(@unserialize($message));
				$message = WCF::getLanguage()->getDynamicVariable('wcf.chat.message.'.$this->type);
			break;
			case self::TYPE_WHISPER:
				$message = @unserialize($message);
				$message = $message['message'];
			case self::TYPE_NORMAL:
			case self::TYPE_ME:
				if (!$this->enableHTML && $outputType == 'text/html') {
					$message = \wcf\system\bbcode\SimpleMessageParser::getInstance()->parse($message, true, $this->enableSmilies);
				}
			break;
		}
		return $message;
	}
	
	/**
	 * Returns the formatted username
	 *
	 * @return	string
	 */
	public function getFormattedUsername() {
		$username = $this->getUsername();
		
		if ($this->type != self::TYPE_INFORMATION && $this->type != self::TYPE_ERROR) $username = \wcf\util\ChatUtil::gradient($username, $this->color1, $this->color2);
		if ($this->type == self::TYPE_WHISPER) {
			$message = @unserialize($this->message);
			$username .= ' -> '.$message['username'];
		}
		
		return '<strong>'.$username.'</strong>';
	}
	
	/**
	 * Returns the unformatted username.
	 *
	 * @return	string
	 */
	public function getUsername() {
		if ($this->type == self::TYPE_INFORMATION) return WCF::getLanguage()->get('wcf.chat.information');
		if ($this->type == self::TYPE_ERROR) return WCF::getLanguage()->get('wcf.chat.error');
		
		return $this->username;
	}
	
	/**
	 * Converts this message into json-form.
	 *
	 * @param	boolean	$raw
	 * @return	string
	 */
	public function jsonify($raw = false) {
		$array = array(
			'formattedUsername' => $this->getFormattedUsername(),
			'formattedMessage' => (string) $this,
			'formattedTime' => \wcf\util\DateUtil::format(\wcf\util\DateUtil::getDateTimeByTimestamp($this->time), 'H:i:s'),
			'separator' => ($this->type == self::TYPE_NORMAL) ? ': ' : ' ',
			'message' => $this->getFormattedMessage('text/plain'),
			'sender' => (int) $this->sender,
			'username' => $this->getUsername(),
			'time' => (int) $this->time,
			'receiver' => (int) $this->receiver,
			'type' => (int) $this->type,
			'roomID' => (int) $this->roomID,
			'messageID' => (int) $this->messageID
		);
		
		if ($raw) return $array;
		return \wcf\util\JSON::encode($array);
	}
}
