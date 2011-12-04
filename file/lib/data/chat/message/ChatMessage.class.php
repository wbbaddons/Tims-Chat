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
		return $this->getFormattedMessage;
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
		}
		return $message;
	}
	
	/**
	 * Returns the formatted username
	 *
	 * @return	string
	 */
	public function getFormattedUsername() {
		$string = str_split($this->username);
		$r = (int) (($this->color1 >> 16 & 255) - ($this->color2 >> 16 & 255)) / (count($string) - 1);
		$g = (int)  (($this->color1 >> 8 & 255) - ($this->color2 >> 8 & 255)) / (count($string) - 1);
		$b = (int)  (($this->color1 & 255) - ($this->color2 & 255)) / (count($string) - 1);
		$result = '';
		for ($i = 0, $max = count($string); $i < $max; $i++) {
			$result .= '<span style="color:rgb('.(($this->color1 >> 16 & 255) - $i * $r).', '.(($this->color1 >> 8 & 255) - $i * $g).', '.(($this->color1 & 255) - $i * $b).')">'.$string[$i].'</span>'; 
		}
		
		return '<strong>'.$result.'</strong>';
	}
}
