<?php
namespace chat\system\user\notification\event;

/**
 * 
 * 
 * @author	Tim Düsterhus
 * @copyright	2010-2014 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	system.user.notification.event
 */
class InvitedUserNotificationEvent extends \wcf\system\user\notification\event\AbstractUserNotificationEvent {
	/**
	 * @see	\wcf\system\user\notification\event\AbstractUserNotificationEvent::$stackable
	 */
	protected $stackable = false;

	/**
	 * @see	\wcf\system\user\notification\event\IUserNotificationEvent::getAuthorID()
	 */
	public function getAuthorID() {
		return $this->getAuthor()->userID;
	}
	
	/**
	 * @see	\wcf\system\user\notification\event\IUserNotificationEvent::getAuthor()
	 */
	public function getAuthor() {
		// TODO: caching
		return new \wcf\data\user\UserProfile(new \wcf\data\user\User($this->additionalData['userID']));
	}
	
	/**
	 * @see	\wcf\system\user\notification\event\IUserNotificationEvent::getTitle()
	 */
	public function getTitle() {
		return $this->getLanguage()->get('chat.notification.invited.title');
	}
	
	/**
	 * @see	\wcf\system\user\notification\event\IUserNotificationEvent::getMessage()
	 */
	public function getMessage() {
		return $this->getLanguage()->getDynamicVariable('chat.notification.invited.message', array(
			'userNotificationObject' => $this->userNotificationObject,
			'author' => $this->author
		));
	}

	/**
	 * @see	\wcf\system\user\notification\event\IUserNotificationEvent::getLink()
	 */
	public function getLink() {
		return \wcf\system\request\LinkHandler::getInstance()->getLink('Chat', array(
			'application' => 'chat',
			'object' => $this->userNotificationObject->getDecoratedObject()
		));
	}
	
	/**
	 * @see	\wcf\system\user\notification\event\IUserNotificationEvent::supportsEmailNotification()
	 */
	public function supportsEmailNotification() {
		return false;
	}
	
	/**
	 * @see	\wcf\system\user\notification\event\IUserNotificationEvent::checkAccess()
	 */
	public function checkAccess() {
		// TODO
		return true;
	}
}
