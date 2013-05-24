<?php
namespace chat\system\user\online\location;
use chat\data;

/**
 * Implementation of IUserOnlineLocation for the chat.
 * 
 * @author 	Tim Düsterhus
 * @copyright	2010-2013 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	system.user.online.location
 */
class ChatLocation implements \wcf\system\user\online\location\IUserOnlineLocation {
	/**
	 * @see	\wcf\system\user\online\location\IUserOnlineLocation::cache()
	 */
	public function cache(\wcf\data\user\online\UserOnline $user) {}
	
	/**
	 * @see	\wcf\system\user\online\location\IUserOnlineLocation::get()
	 */
	public function get(\wcf\data\user\online\UserOnline $user, $languageVariable = '') {
		$rooms = data\room\RoomCache::getInstance()->getRooms();
		
		if (isset($cache[$user->objectID])) {
			if ($cache[$user->objectID]->canEnter()) {
				return \wcf\system\WCF::getLanguage()->getDynamicVariable($languageVariable, array(
					'room' => $cache[$user->objectID]
				));
			}
		}
		
		return '';
	}
}
