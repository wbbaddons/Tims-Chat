<?php
namespace wcf\data\chat\message;
use \wcf\system\Regex;
use \wcf\system\WCF;
use \wcf\util\ChatUtil;

/**
 * Represents a chat message.
 *
 * @author	Tim Düsterhus
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
	 * cache for users
	 * @var array<\wcf\data\user\User>
	 */
	protected static $users = array();
	
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
				$message = WCF::getLanguage()->getDynamicVariable('wcf.chat.message.'.$this->type, unserialize($message) ?: array());
			break;
			case self::TYPE_MODERATE:
				$message = unserialize($message);
				$message = WCF::getLanguage()->getDynamicVariable('wcf.chat.message.'.$this->type.'.'.$message['type'], $message ?: array());
				$message = self::replaceUserLink($message, $outputType);
			break;
			case self::TYPE_WHISPER:
				$message = unserialize($message);
				$message = $message['message'];
			case self::TYPE_NORMAL:
			case self::TYPE_ME:
				if ($this->enableBBCodes) {
					$messageParser = \wcf\system\bbcode\MessageParser::getInstance();
					$messageParser->setOutputType($outputType);
					$message = $messageParser->parse($message, $this->enableSmilies, $this->enableHTML, true, false);
				}
				else if (!$this->enableHTML && $outputType == 'text/html') {
					$message = \wcf\system\bbcode\SimpleMessageParser::getInstance()->parse($message, true, $this->enableSmilies);
				}
			break;
			default:
				if ($this->enableHTML) {
					$message = self::replaceUserLink($message, $outputType);
				}
				
				if ($this->enableBBCodes) {
					$messageParser = \wcf\system\bbcode\MessageParser::getInstance();
					$messageParser->setOutputType($outputType);
					$message = $messageParser->parse($message, $this->enableSmilies, $this->enableHTML, true, false);
				}
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
		if ($this->type == self::TYPE_INFORMATION) return WCF::getLanguage()->get('wcf.chat.information');
		if ($this->type == self::TYPE_ERROR) return WCF::getLanguage()->get('wcf.chat.error');
		
		if ($colored) {
			$username = \wcf\util\ChatUtil::gradient($username, $this->color1, $this->color2);
		}
		
		if ($this->type == self::TYPE_WHISPER) {
			$message = unserialize($this->message);
			$username .= ' -> '.$message['username'];
		}
		
		return $username;
	}
	
	/**
	 * Replaces a userLink in a message.
	 */
	public static function replaceUserLink($message, $outputType) {
		static $regex = null;
		if ($regex === null) $regex = new Regex('<span class="userLink" data-user-id="(\d+)" />');
		
		if ($outputType === 'text/html') {
			return $regex->replace($message, new \wcf\system\Callback(function ($matches) {
				return self::getUserLink($matches[1]);
			}));
		}
		else {
			return $regex->replace($message, new \wcf\system\Callback(function ($matches) {
				self::getUserLink($matches[1]);
				
				return self::$users[$matches[1]]->username;
			}));
		}
	}
	
	/**
	 * Returns a fully colored userlink.
	 */
	public static function getUserLink($userID) {
		if (!isset(self::$users[$userID])) {
			self::$users[$userID] = $user = new \wcf\data\user\User($userID);
			
			// Username + link to profile
			$color = ChatUtil::readUserData('color', $user);
			$profile = \wcf\system\request\LinkHandler::getInstance()->getLink('User', array(
				'object' => $user
			));
			self::$users[$userID]->userLink = '<a href="'.$profile.'" class="userLink" data-user-id="'.$user->userID.'">'.ChatUtil::gradient($user->username, $color[1], $color[2]).'</a>';
		}
		
		return self::$users[$userID]->userLink;
	}
	
	/**
	 * Converts this message into json-form.
	 *
	 * @param	boolean	$raw
	 * @return	string
	 */
	public function jsonify($raw = false) {
		switch ($this->type) {
			case self::TYPE_NORMAL:
			case self::TYPE_ERROR:
			case self::TYPE_INFORMATION:
			case self::TYPE_WHISPER:
				$separator = ':';
			break;
			default:
				$separator = ' ';
			break;
		}
		
		$array = array(
			'formattedUsername' => $this->getUsername(true),
			'formattedMessage' => $this->getFormattedMessage(),
			'formattedTime' => \wcf\util\DateUtil::format(\wcf\util\DateUtil::getDateTimeByTimestamp($this->time), 'H:i:s'),
			'separator' => $separator,
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
