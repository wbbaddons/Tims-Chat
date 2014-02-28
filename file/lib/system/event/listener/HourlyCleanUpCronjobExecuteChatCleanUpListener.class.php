<?php
namespace chat\system\event\listener;
use \chat\data;

/**
 * Vaporizes unneeded data.
 * 
 * @author 	Tim Düsterhus
 * @copyright	2010-2014 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	system.event.listener
 */
class HourlyCleanUpCronjobExecuteChatCleanUpListener implements \wcf\system\event\IEventListener {
	/**
	 * @see	\wcf\system\event\IEventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		$messageAction = new data\message\MessageAction(array(), 'prune');
		$messageAction->executeAction();
		$roomAction = new data\room\RoomAction(array(), 'prune');
		$roomAction->executeAction();
		
		// kill dead users
		$roomAction = new data\room\RoomAction(array(), 'removeDeadUsers');
		$roomAction->executeAction();
	}
}
