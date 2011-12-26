<?php
namespace wcf\data\chat\message;
use \wcf\system\WCF;

/**
 * Represents a chat message.
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2011 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	timwolla.wcf.chat
 * @subpackage	data.chat.message
 */
class ChatMessage extends \wcf\data\DatabaseObject {
	/**
	 * @see	wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'chat_message';
	
	/**
	 * @see	wcf\data\DatabaseObject::$databaseTableIndexName
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
	
	/**
	 * Returns the message.
	 *
	 * @return	string
	 */
	public function __toString() {
		return $this->getFormattedMessage();
	}
	
	/**
	 * Returns the formatted message.
	 *
	 * @return	string
	 */
	public function getFormattedMessage() {
		$message = $this->message;
		switch ($this->type) {
			case self::TYPE_JOIN:
			case self::TYPE_LEAVE:
			case self::TYPE_BACK:
				$message = WCF::getLanguage()->get('wcf.chat.message.'.$this->type);
			break;
			case self::TYPE_NORMAL:
				if (!$this->enableHTML) {
					$message = \wcf\system\bbcode\SimpleMessageParser::getInstance()->parse($message, true, $this->enableSmilies);
				}
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
		
		if ($this->type != self::TYPE_INFORMATION) $username = \wcf\util\ChatUtil::gradient($username, $this->color1, $this->color2);
		
		return '<strong>'.$username.'</strong>';
	}
	
	/**
	 * Returns the unformatted username.
	 *
	 * @return	string
	 */
	public function getUsername() {
		if ($this->type == self::TYPE_INFORMATION) return WCF::getLanguage()->get('wcf.chat.information');
		return $this->username;
	}
	
	/**
	 * Converts this message into json-form.
	 *
	 * @return	string
	 */
	public function jsonify() {
		return \wcf\util\JSON::encode(array(
			'formattedUsername' => $this->getFormattedUsername(),
			'formattedMessage' => (string) $this,
			'formattedTime' => \wcf\util\DateUtil::format(\wcf\util\DateUtil::getDateTimeByTimestamp($this->time), 'H:i:s'),
			'sender' => $this->sender,
			'username' => $this->getUsername(),
			'time' => $this->time,
			'receiver' => $this->receiver,
			'type' => $this->type,
			'roomID' => $this->roomID
		));
	}
}
