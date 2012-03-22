<?php
namespace wcf\data\chat\message;

/**
 * Represents a list of chat messages.
 * 
 * @author 	Tim Düsterhus
 * @copyright	2010-2012 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.wcf.chat
 * @subpackage	data.chat.room
 */
class ChatMessageList extends \wcf\data\DatabaseObjectList {
	/**
	 * @see	\wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\chat\message\ChatMessage';
	
	/**
	 * Reads the newest messages for the given room.
	 *
	 * @param	\wcf\data\chat\room\ChatRoom	$room
	 * @param	integer				$number
	 * @return	array<\wcf\data\chat\message\ChatMessage>
	 */
	public static function getNewestMessages(\wcf\data\chat\room\ChatRoom $room, $number = CHAT_LASTMESSAGES) {
		$messageList = new static();
		$messageList->sqlOrderBy = "chat_message.messageID DESC";
		$messageList->sqlLimit = $number;
		$messageList->getConditionBuilder()->add('
			((
					chat_message.receiver IS NULL
				AND 	chat_message.roomID = ?
			)
			OR chat_message.receiver = ?
			OR chat_message.sender = ?)', array($room->roomID, \wcf\system\WCF::getUser()->userID, \wcf\system\WCF::getUser()->userID));
		
		$messageList->readObjects();
		return array_reverse($messageList->getObjects());
	}
	
	/**
	 * Reads the messages since the given message-id for the given room.
	 *
	 * @param	\wcf\data\chat\room\ChatRoom	$room
	 * @param	integer				$since
	 * @return	array<\wcf\data\chat\message\ChatMessage>
	 */
	public static function getMessagesSince(\wcf\data\chat\room\ChatRoom $room, $since) {
		$messageList = new static();
		$messageList->sqlOrderBy = "chat_message.messageID ASC";
		$messageList->getConditionBuilder()->add('chat_message.messageID > ?', array($since));
		$messageList->getConditionBuilder()->add('
			((
					chat_message.receiver IS NULL
				AND 	chat_message.roomID = ?
			)
			OR chat_message.receiver = ?
			OR chat_message.sender = ?)', array($room->roomID, \wcf\system\WCF::getUser()->userID, \wcf\system\WCF::getUser()->userID));
		
		$messageList->readObjects();
		return $messageList->getObjects();
	}
}
