<?php
namespace chat\data\room;

/**
 * Represents a list of chat rooms.
 * 
 * @author 	Tim Düsterhus
 * @copyright	2010-2014 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	data.room
 */
class RoomList extends \wcf\data\DatabaseObjectList {
	/**
	 * @see	\wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'chat\data\room\Room';
}
