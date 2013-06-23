<?php
namespace chat\data\room;
use \chat\data\message;
use \chat\util\ChatUtil;
use \wcf\system\exception;
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
	 * Resets cache if any of the listed actions is invoked
	 * @var	array<string>
	 */
	protected $resetCache = array('create', 'delete', 'toggle', 'update', 'updatePosition', 'prune');
	
	/**
	 * Fixes create to append new rooms.
	 */
	public function create() {
		$room = parent::create();
		
		WCF::getDB()->beginTransaction();
		$sql = "SELECT	MAX(showOrder)
			FROM	".call_user_func(array($this->className, 'getDatabaseTableName'))."
			FOR UPDATE";
		$stmt = WCF::getDB()->prepareStatement($sql);
		$stmt->execute();

		$editor = new RoomEditor($room);
		$editor->update(array('showOrder' => ($stmt->fetchColumn() + 1)));
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
							field = ?
						AND	fieldValue IS NOT NULL
				)";
		$stmt = \wcf\system\WCF::getDB()->prepareStatement($sql);
		$stmt->execute(array(0, 'roomID'));
		$objectIDs = array();
		
		while ($objectID = $stmt->fetchColumn()) $objectIDs[] = $objectID;
		
		return call_user_func(array($this->className, 'deleteAll'), $objectIDs);
	}
	
	/**
	 * @see wcf\data\ISortableAction::validateUpdatePosition()
	 */
	public function validateUpdatePosition() {
		// validate permissions
		if (is_array($this->permissionsUpdate) && count($this->permissionsUpdate)) {
			WCF::getSession()->checkPermissions($this->permissionsUpdate);
		}
		else {
			throw new exception\PermissionDeniedException();
		}
		
		if (!isset($this->parameters['data']['structure'])) {
			throw new exception\UserInputException('structure');
		}
	}
	
	/**
	 * @see wcf\data\ISortableAction::updatePosition()
	 */
	public function updatePosition() {
		$roomList = new RoomList();
		$roomList->readObjects();
		
		$i = 0;
		WCF::getDB()->beginTransaction();
		foreach ($this->parameters['data']['structure'][0] as $roomID) {
			$room = $roomList->search($roomID);
			if ($room === null) continue;
			
			$editor = new RoomEditor($room);
			$editor->update(array('showOrder' => $i++));
		}
		WCF::getDB()->commitTransaction();
	}
	
	/**
	 * Validates parameters and permissions.
	 */
	public function validateGetRoomList() {
		if (!MODULE_CHAT) throw new exception\IllegalLinkException();
		
		$this->parameters['room'] = RoomCache::getInstance()->getRoom(WCF::getUser()->chatRoomID);
		if ($this->parameters['room'] === null) throw new exception\IllegalLinkException();
	}
	
	/**
	 * Returns the available rooms.
	 */
	public function getRoomList() {
		$rooms = RoomCache::getInstance()->getRooms();
		
		$result = array();
		foreach ($rooms as $room) {
			if (!$room->canEnter()) continue;
			
			$result[] = array(
				'title' => (string) $room,
				'link' => \wcf\system\request\LinkHandler::getInstance()->getLink('Chat', array(
					'application' => 'chat',
					'object' => $room
				)),
				'roomID' => $room->roomID,
				'active' => $room->roomID == $this->parameters['room']->roomID
			);
		}
		
		return $result;
	}
	
	/**
	 * Validates parameters and permissions.
	 */
	public function validateJoin() {
		if (!MODULE_CHAT) throw new exception\IllegalLinkException();
		
		unset($this->parameters['user']);
		$this->readInteger('roomID');
		
		$room = RoomCache::getInstance()->getRoom($this->parameters['roomID']);
		if ($room === null) throw new exception\UserInputException();
		if (!$room->canEnter()) throw new exception\PermissionDeniedException();
	}
	
	/**
	 * Joins the room.
	 */
	public function join() {
		// user cannot be set during an AJAX request but may be set by the chat itself
		if (!isset($this->parameters['user'])) {
			$this->parameters['user'] = WCF::getUser();
		}
		
		$room = RoomCache::getInstance()->getRoom($this->parameters['roomID']);
		if ($room === null) throw new exception\UserInputException();
		
		if (CHAT_DISPLAY_JOIN_LEAVE) {
			if ($this->parameters['user']->chatRoomID) {
				// leave message
				$messageAction = new message\MessageAction(array(), 'create', array(
					'data' => array(
						'roomID' => $this->parameters['user']->chatRoomID,
						'sender' => $this->parameters['user']->userID,
						'username' => $this->parameters['user']->username,
						'time' => TIME_NOW,
						'type' => message\Message::TYPE_LEAVE,
						'message' => '',
						'color1' => $this->parameters['user']->chatColor1,
						'color2' => $this->parameters['user']->chatColor2,
						'additionalData' => serialize(array('room' => $room))
					)
				));
				$messageAction->executeAction();
			}
			
			$ipAddress = '';
			if ($this->parameters['user']->userID == WCF::getUser()->userID) $ipAddress = \wcf\util\UserUtil::convertIPv6To4(\wcf\util\UserUtil::getIpAddress());
			
			// join message
			$messageAction = new message\MessageAction(array(), 'create', array(
				'data' => array(
					'roomID' => $room->roomID,
					'sender' => $this->parameters['user']->userID,
					'username' => $this->parameters['user']->username,
					'time' => TIME_NOW,
					'type' => message\Message::TYPE_JOIN,
					'message' => '',
					'color1' => $this->parameters['user']->chatColor1,
					'color2' => $this->parameters['user']->chatColor2,
					'additionalData' => serialize(array('ipAddress' => $ipAddress))
				)
			));
			$messageAction->executeAction();
		}
		
		$newestMessages = message\ViewableMessageList::getNewestMessages($room, CHAT_LASTMESSAGES + 1);
		
		try {
			$lastSeen = end($newestMessages)->messageID;
		}
		catch (\wcf\system\exception\SystemException $e) {
			$lastSeen = 0;
		}
		
		$editor = new \wcf\data\user\UserEditor($this->parameters['user']);
		$editor->update(array(
			'chatRoomID' => $room->roomID,
			'chatAway' => null,
			'chatLastActivity' => TIME_NOW,
			'chatLastSeen' => $lastSeen
		));
		
		// add activity points
		$microtime = microtime(true) * 1000;
		$result = $microtime & 0xFFFFFFFF;
		if ($result > 0x7FFFFFFF) $result -= 0x80000000;
		\wcf\system\user\activity\point\UserActivityPointHandler::getInstance()->fireEvent('be.bastelstu.chat.activityPointEvent.join', $result, WCF::getUser()->userID);
		
		// break if not using ajax
		\wcf\system\nodePush\NodePushHandler::getInstance()->sendMessage('be.bastelstu.chat.join');
		
		$messages = array();
		foreach ($newestMessages as $message) $messages[] = $message->jsonify(true);
		return array(
			'title' => (string) $room,
			'topic' => $room->getTopic(),
			'messages' => $messages
		);
	}
	
	/**
	 * Validates parameters and permissions.
	 */
	public function validateLeave() {
		if (!MODULE_CHAT) throw new exception\IllegalLinkException();
		
		unset($this->parameters['user']);
		
		if (RoomCache::getInstance()->getRoom(WCF::getUser()->chatRoomID) === null) throw new exception\IllegalLinkException();
	}
	
	/**
	 * Leaves the room.
	 */
	public function leave() {
		// user cannot be set during an AJAX request but may be set by the chat itself
		if (!isset($this->parameters['user'])) {
			$this->parameters['user'] = WCF::getUser();
		}
		
		$room = RoomCache::getInstance()->getRoom($this->parameters['user']->chatRoomID);
		if ($room === null) throw new exception\UserInputException();
		
		if (CHAT_DISPLAY_JOIN_LEAVE) {
			// leave message
			$messageAction = new message\MessageAction(array(), 'create', array(
				'data' => array(
					'roomID' => $room->roomID,
					'sender' => $this->parameters['user']->userID,
					'username' => $this->parameters['user']->username,
					'time' => TIME_NOW,
					'type' => message\Message::TYPE_LEAVE,
					'message' => '',
					'color1' => $this->parameters['user']->chatColor1,
					'color2' => $this->parameters['user']->chatColor2
				)
			));
			$messageAction->executeAction();
		}
		
		// set current room to null
		$editor = new \wcf\data\user\UserEditor($this->parameters['user']);
		$editor->update(array(
			'chatRoomID' => null
		));
		
		\wcf\system\nodePush\NodePushHandler::getInstance()->sendMessage('be.bastelstu.chat.join');
	}
	
	/**
	 * Forces dead users to leave the chat.
	 */
	public function removeDeadUsers() {
		$deadUsers = Room::getDeadUsers();
		
		foreach ($deadUsers as $deadUser) {
			$roomAction = new self(array(), 'leave', array(
				'user' => $deadUser
			));
			$roomAction->executeAction();
		}
	}
	
	/**
	 * Validates permissions.
	 */
	public function validateGetDashboardRoomList() {
		if (!MODULE_CHAT) throw new \wcf\system\exception\IllegalLinkException();
	}
	
	/**
	 * Returns dashboard roomlist.
	 */
	public function getDashboardRoomList() {
		$rooms = RoomCache::getInstance()->getRooms();
		
		foreach ($rooms as $key => $room) {
			if (!$room->canEnter()) unset($rooms[$key]);
		}
		
		\wcf\system\WCF::getTPL()->assign(array(
			'rooms' => $rooms,
			'onlyList' => true
		));
		
		return array(
			'template' => \wcf\system\WCF::getTPL()->fetch('dashboardBoxOnlineList', 'chat')
		);
	}
}
