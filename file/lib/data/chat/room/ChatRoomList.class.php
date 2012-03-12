<?php
namespace wcf\data\chat\room;

/**
 * Represents a list of chat rooms.
 * 
 * @author 	Tim Düsterhus
 * @copyright	2010-2012 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.wcf.chat
 * @subpackage	data.chat.room
 */
class ChatRoomList extends \wcf\data\DatabaseObjectList {
	/**
	 * @see	\wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\chat\room\ChatRoom';
}
