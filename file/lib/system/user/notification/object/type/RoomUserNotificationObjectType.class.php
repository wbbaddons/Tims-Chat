<?php
namespace chat\system\user\notification\object\type;

/**
 * Chat room user notification object type.
 * 
 * @author	Tim Düsterhus
 * @copyright	2010-2014 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	system.user.notification.object.type
 */
class RoomUserNotificationObjectType extends \wcf\system\user\notification\object\type\AbstractUserNotificationObjectType {
	/**
	 * @see	\wcf\system\user\notification\object\type\AbstractUserNotificationObjectType::$decoratorClassName
	 */
	protected static $decoratorClassName = 'chat\system\user\notification\object\RoomUserNotificationObject';
	
	/**
	 * @see	\wcf\system\user\notification\object\type\AbstractUserNotificationObjectType::$objectClassName
	 */
	protected static $objectClassName = 'chat\data\room\Room';
	
	/**
	 * @see	\wcf\system\user\notification\object\type\AbstractUserNotificationObjectType::$objectListClassName
	 */
	protected static $objectListClassName = 'chat\data\room\RoomList';
}
