<?php
namespace wcf\data\chat\room;

/**
 * Provides functions to edit chat rooms.
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2011 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	timwolla.wcf.chat
 * @subpackage	data.chat.room
 */
class ChatRoomEditor extends \wcf\data\DatabaseObjectEditor {
	/**
	 * @see	wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = '\wcf\data\chat\room\ChatRoom';
	
	/**
	 * @see	wcf\data\IEditableObject::create()
	 */
	public static function create(array $parameters = array()) {
		$room = parent::create($parameters);
		
		self::clearCache();
		
		return $room;
	}
	
	/**
	 * @see	wcf\data\IEditableObject::update()
	 */
	public function update(array $parameters = array()) {
		parent::update($parameters);
		
		self::clearCache();
	}
	
	/**
	 * @see	wcf\data\IEditableObject::update()
	 */
	public static function deleteAll(array $objectIDs = array()) {
		parent::deleteAll($objectIDs);
		
		self::clearCache();
	}
}
