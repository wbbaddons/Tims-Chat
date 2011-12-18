<?php
namespace wcf\system\event\listener;

/**
 * Adds a new route to RouteHandler
 *
 * @author 	Maximilian Mader
 * @copyright	2010-2011 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	timwolla.wcf.chat
 * @subpackage system.event.listener
 */
class ChatRouteListener implements \wcf\system\event\IEventListener {
	/**
	 * @see wcf\system\event\IEventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		$route = new \wcf\system\request\Route('chatAction');
		$route->setSchema('/{controller}/{action}');
		$route->setParameterOption('controller', null, 'Chat');
		$route->setParameterOption('action', null, '(Log|Send|RefreshRoomList)');
		$eventObj->addRoute($route);
	}
}
