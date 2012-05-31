<?php
namespace be\bastelstu\wcf\chat;

/**
 * Handles updates of Tims Chat.
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2012 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.wcf.chat
 */
final class Update {
	/**
	 * Contains all the rooms the current installation has.
	 * 
	 * @var array<\wcf\data\chat\room\ChatRoom>
	 */
	private $rooms = null;
	
	public function __construct() {
		$this->rooms = \wcf\data\chat\room\ChatRoom::getCache();
	}
	
	/**
	 * Notifies users to refresh the chat as the JS may no longer be fully compatible with the PHP code.
	 */
	public function execute() {
		foreach ($this->rooms as $room) {
			$messageAction = new \wcf\data\chat\message\ChatMessageAction(array(), 'create', array(
				'data' => array(
					'roomID' => $room->roomID,
					'time' => TIME_NOW,
					'type' => \wcf\data\chat\message\ChatMessage::TYPE_INFORMATION,
					// TODO: Language item
					'message' => 'Tims Chat was updated. Please refresh the page.'
				)
			));
			$messageAction->executeAction();
		}
	}
}
$update = new Update();
$update->execute();
