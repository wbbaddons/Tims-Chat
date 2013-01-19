<?php
namespace chat\data\message;

/**
 * Represents a list of chat messages.
 * 
 * @author 	Tim Düsterhus
 * @copyright	2010-2013 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	chat.room
 */
class MessageList extends \wcf\data\DatabaseObjectList {
	/**
	 * @see	\wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'chat\data\message\Message';
	
	/**
	 * Reads the newest messages for the given room.
	 *
	 * @param	\chat\data\room\Room	$room
	 * @param	integer			$number
	 * @return	array<\chat\data\message\Message>
	 */
	public static function getNewestMessages(\chat\data\room\Room $room, $number = CHAT_LASTMESSAGES) {
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
	 * @param	\chat\data\room\Room	$room
	 * @param	integer			$since
	 * @return	array<\chat\data\message\Message>
	 */
	public static function getMessagesSince(\chat\data\room\Room $room, $since) {
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
