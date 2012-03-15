<?php
namespace wcf\data\chat\room;
use \wcf\system\WCF;

/**
 * Executes chatroom-related actions.
 * 
 * @author 	Tim Düsterhus
 * @copyright	2010-2012 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	timwolla.wcf.chat
 * @subpackage	data.chat.room
 */
class ChatRoomAction extends \wcf\data\AbstractDatabaseObjectAction {
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = '\wcf\data\chat\room\ChatRoomEditor';
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$permissionsDelete
	 */
	protected $permissionsDelete = array('admin.content.chat.canDeleteRoom');
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$permissionsUpdate
	 */
	protected $permissionsUpdate = array('admin.content.chat.canEditRoom');
	
	/**
	 * Fixes create to append new rooms.
	 */
	public function create() {
		$room = parent::create();
		
		WCF::getDB()->beginTransaction();
		$sql = "SELECT		max(position) as max
			FROM		wcf".WCF_N."_chat_room
			FOR UPDATE";
		$stmt = WCF::getDB()->prepareStatement($sql);
		$stmt->execute();
		$row = $stmt->fetchArray();
		
		$sql = "UPDATE	wcf".WCF_N."_chat_room
			SET	position = ".($row['max'] + 1)."
			WHERE	roomID = ?";
		$stmt = WCF::getDB()->prepareStatement($sql);
		$stmt->execute(array($room->roomID));
		WCF::getDB()->commitTransaction();
		
		return $room;
	}
	
	/**
	 * Validates parameters to update sorting.
	 */
	public function validateUpdatePosition() {
		// validate permissions
		if (is_array($this->permissionsUpdate) && count($this->permissionsUpdate)) {
			try {
				WCF::getSession()->checkPermissions($this->permissionsUpdate);
			}
			catch (\wcf\system\exception\PermissionDeniedException $e) {
				throw new ValidateActionException('Insufficient permissions');
			}
		}
		else {
			throw new ValidateActionException('Insufficient permissions');
		}
		
		if (!isset($this->parameters['data']['structure'])) {
			throw new ValidateActionException('Missing parameter structure');
		}
	}
	
	/**
	 * Updates sorting.
	 */
	public function updatePosition() {
		$roomList = new \wcf\data\chat\room\ChatRoomList();
		$roomList->sqlOrderBy = "chat_room.position";
		$roomList->sqlLimit = 0;
		$roomList->readObjects();
		
		$i = 0;
		WCF::getDB()->beginTransaction();
		foreach ($this->parameters['data']['structure'][0] as $roomID) {
			$room = $roomList->search($roomID);
			if ($room === null) continue;
			$editor = new ChatRoomEditor($room);
			$editor->update(array('position' => $i++));
		}
		WCF::getDB()->commitTransaction();
	}
}
