<?php
namespace chat\data\room;
use \wcf\system\WCF;

/**
 * Executes chatroom-related actions.
 * 
 * @author 	Tim Düsterhus
 * @copyright	2010-2013 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	data.room
 */
class RoomAction extends \wcf\data\AbstractDatabaseObjectAction implements \wcf\data\ISortableAction {
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = '\chat\data\room\RoomEditor';
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$permissionsDelete
	 */
	protected $permissionsDelete = array('admin.chat.canDeleteRoom');
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$permissionsUpdate
	 */
	protected $permissionsUpdate = array('admin.chat.canEditRoom');
	
	/**
	 * Fixes create to append new rooms.
	 */
	public function create() {
		$room = parent::create();
		
		WCF::getDB()->beginTransaction();
		$sql = "SELECT	MAX(position)
			FROM	".call_user_func(array($this->className, 'getDatabaseTableName'))."
			FOR UPDATE";
		$stmt = WCF::getDB()->prepareStatement($sql);
		$stmt->execute();

		$editor = new RoomEditor($room);
		$editor->update(array('position' => ($stmt->fetchColumn() + 1)));
		WCF::getDB()->commitTransaction();
		
		return $room;
	}
	
	/**
	 * Deletes temporary rooms that are unused.
	 * 
	 * @return	integer		Number of deleted rooms
	 */
	public function prune() {
		$sql = "SELECT
				".call_user_func(array($this->className, 'getDatabaseTableIndexName'))."
			FROM
				".call_user_func(array($this->className, 'getDatabaseTableName'))."
			WHERE
					permanent = ?
				AND 	roomID NOT IN (
					SELECT
						fieldValue AS roomID 
					FROM
						wcf".WCF_N."_user_storage
					WHERE
						AND	field = ?
						AND 	fieldValue IS NOT NULL
				)";
		$stmt = \wcf\system\WCF::getDB()->prepareStatement($sql);
		$stmt->execute(array(0, 'roomID'));
		$objectIDs = array();
		
		while ($objectIDs[] = $stmt->fetchColumn());
		
		return call_user_func(array($this->className, 'deleteAll'), $objectIDs);
	}
	
	/**
	 * @see wcf\data\ISortableAction
	 */
	public function validateUpdatePosition() {
		// validate permissions
		if (is_array($this->permissionsUpdate) && count($this->permissionsUpdate)) {
			WCF::getSession()->checkPermissions($this->permissionsUpdate);
		}
		else {
			throw new \wcf\system\exception\PermissionDeniedException();
		}
		
		if (!isset($this->parameters['data']['structure'])) {
			throw new \wcf\system\exception\UserInputException('structure');
		}
		
		if (!isset($this->parameters['data']['offset'])) $this->parameters['data']['offset'] = 0;
	}
	
	/**
	 * @see wcf\data\ISortableAction
	 */
	public function updatePosition() {
		$roomList = new RoomList();
		$roomList->sqlOrderBy = "chat_room.position";
		$roomList->readObjects();
		
		$i = $this->parameters['data']['offset'];
		WCF::getDB()->beginTransaction();
		foreach ($this->parameters['data']['structure'][0] as $roomID) {
			$room = $roomList->search($roomID);
			if ($room === null) continue;
			$editor = new RoomEditor($room);
			$editor->update(array('position' => $i++));
		}
		WCF::getDB()->commitTransaction();
	}
}
