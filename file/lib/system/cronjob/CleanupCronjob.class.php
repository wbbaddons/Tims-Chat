<?php
namespace chat\system\cronjob;
use \chat\data;

/**
 * Vaporizes unneeded data.
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2013 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	system.cronjob
 */
class ChatCleanupCronjob implements \wcf\system\cronjob\ICronjob {
	/**
	 * @see wcf\system\ICronjob::execute()
	 */
	public function execute(\wcf\data\cronjob\Cronjob $cronjob) {
		$messageAction = new data\message\MessageAction(array(), 'prune');
		$messageAction->executeAction();
		$roomAction = new data\room\RoomAction(array(), 'prune');
		$roomAction->executeAction();
		$suspensionAction = new data\suspension\SuspensionAction(array(), 'prune');
		$suspensionAction->executeAction();
		
		// kill dead users
		$deadUsers = \chat\util\ChatUtil::getDiedUsers();
		foreach ($deadUsers as $deadUser) {
			if (!$deadUser) continue;
			
			$user = new \wcf\data\user\User($deadUser['userID']);
			if (CHAT_DISPLAY_JOIN_LEAVE) {
				$userData['color'] = \chat\util\ChatUtil::readUserData('color', $user);
			
				$messageAction = new data\message\MessageAction(array(), 'create', array(
					'data' => array(
						'roomID' => $deadUser['roomID'],
						'sender' => $user->userID,
						'username' => $user->username,
						'time' => TIME_NOW,
						'type' => data\message\Message::TYPE_LEAVE,
						'message' => '',
						'color1' => $userData['color'][1],
						'color2' => $userData['color'][2]
					)
				));
				$messageAction->executeAction();
			}
			\chat\util\ChatUtil::writeUserData(array('roomID' => null), $user);
		}
	}
}
