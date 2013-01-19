<?php
namespace chat\system\event\listener;

/**
 * Adds a new route to RouteHandler
 *
 * @author 	Maximilian Mader
 * @copyright	2010-2013 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	system.event.listener
 */
class RouteHandlerListener implements \wcf\system\event\IEventListener {
	/**
	 * @see	\wcf\system\event\IEventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		$route = new \wcf\system\request\Route('chatAction');
		$route->setSchema('/{controller}/{action}/{id}');
		$route->setParameterOption('controller', null, 'Chat');
		$route->setParameterOption('action', null, '(Message|Log|Send|RefreshRoomList|Copyright|Leave)');
		$route->setParameterOption('id', null, '\d+', true);
		$eventObj->addRoute($route);
	}
}
