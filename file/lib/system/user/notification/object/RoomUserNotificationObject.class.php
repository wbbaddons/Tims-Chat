<?php
namespace chat\system\user\notification\object;

/**
 * 
 * 
 * @author	Tim Düsterhus
 * @copyright	2010-2014 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	system.user.notification.object
 */
class RoomUserNotificationObject extends \wcf\data\DatabaseObjectDecorator implements \wcf\system\user\notification\object\IUserNotificationObject {
	/**
	 * @see	\wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'chat\data\room\Room';
	
	/**
	 * @see	\wcf\system\user\notification\object\IUserNotificationObject::getObjectID()
	 */
	public function getObjectID() {
		return $this->roomID;
	}
	
	/**
	 * @see	\wcf\system\user\notification\object\IUserNotificationObject::getTitle()
	 */
	public function getTitle() {
		return $this->getDecoratedObject()->getTitle();
	}
	
	/**
	 * @see	\wcf\system\user\notification\object\IUserNotificationObject::getURL()
	 */
	public function getURL() {
		return \wcf\system\request\LinkHandler::getInstance()->getLink('Chat', array(
			'application' => 'chat',
			'object' => $this->userNotificationObject->getRoom(),
		));
	}
	
	/**
	 * @see	\wcf\system\user\notification\object\IUserNotificationObject::getAuthorID()
	 */
	public function getAuthorID() {
		// this value is ignored
		return PHP_INT_MAX;
	}
}
