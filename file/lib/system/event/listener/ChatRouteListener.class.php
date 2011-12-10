<?php
namespace wcf\system\event\listener;
use wcf\system\event\IEventListener;
use wcf\system\request\Route;

class ChatRouteListener implements IEventListener {
	/**
	 * @see wcf\system\event\IEventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		$route = new Route('chatAction');
		$route->setSchema('/{controller}/{action}');
		$route->setParameterOption('controller', null, 'Chat');
		$route->setParameterOption('action', null, '(Log|Send)');
		$eventObj->addRoute($route);
	}
}