<?php
namespace wcf\data\chat\message;

/**
 * Represents a list of chat messages.
 * 
 * @author 	Tim Düsterhus
 * @copyright	2010-2011 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	timwolla.wcf.chat
 * @subpackage	data.chat.room
 */
class ChatMessageList extends \wcf\data\DatabaseObjectList {
	/**
	 * @see	wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\chat\message\ChatMessage';
	
	public static function getNewestMessages(\wcf\data\chat\room\ChatRoom $room, $number) {
		$messageList = new static();
		$messageList->sqlOrderBy = "chat_message.messageID DESC";
		$messageList->sqlLimit = $number;
		$messageList->getConditionBuilder()->add('chat_message.roomID = ?', array($room->roomID));
		$messageList->readObjects();
		return array_reverse($messageList->getObjects());
	}
}
