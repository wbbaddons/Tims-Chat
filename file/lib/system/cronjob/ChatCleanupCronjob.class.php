<?php
namespace wcf\system\cronjob;
use \wcf\data\chat;

/**
 * Vaporizes unneeded data.
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2012 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.wcf.chat
 * @subpackage	system.cronjob
 */
class ChatCleanupCronjob implements ICronjob {
	/**
	 * @see wcf\system\ICronjob::execute()
	 */
	public function execute(\wcf\data\cronjob\Cronjob $cronjob) {
		$messageAction = new chat\message\ChatMessageAction(array(), 'prune');
		$messageAction->executeAction();
		$roomAction = new chat\room\ChatRoomAction(array(), 'prune');
		$roomAction->executeAction();
		$suspensionAction = new chat\suspension\ChatSuspensionAction(array(), 'prune');
		$suspensionAction->executeAction();
		
		// kill dead users
		$deadUsers = \wcf\util\ChatUtil::getDiedUsers();
		foreach ($deadUsers as $deadUser) {
			if (!$deadUser) continue;
			
			$user = new \wcf\data\user\User($deadUser['userID']);
			if (CHAT_DISPLAY_JOIN_LEAVE) {
				$userData['color'] = \wcf\util\ChatUtil::readUserData('color', $user);
			
				$messageAction = new chat\message\ChatMessageAction(array(), 'create', array(
					'data' => array(
						'roomID' => $deadUser['roomID'],
						'sender' => $user->userID,
						'username' => $user->username,
						'time' => TIME_NOW,
						'type' => chat\message\ChatMessage::TYPE_LEAVE,
						'message' => '',
						'color1' => $userData['color'][1],
						'color2' => $userData['color'][2]
					)
				));
				$messageAction->executeAction();
			}
			\wcf\util\ChatUtil::writeUserData(array('roomID' => null), $user);
		}
	}
}
