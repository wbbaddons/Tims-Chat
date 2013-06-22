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
class CleanupCronjob implements \wcf\system\cronjob\ICronjob {
	/**
	 * @see wcf\system\ICronjob::execute()
	 */
	public function execute(\wcf\data\cronjob\Cronjob $cronjob) {
		$messageAction = new data\message\MessageAction(array(), 'prune');
		$messageAction->executeAction();
		$roomAction = new data\room\RoomAction(array(), 'prune');
		$roomAction->executeAction();
		
		// kill dead users
		$roomAction = new data\room\RoomAction(array(), 'removeDeadUsers');
		$roomAction->executeAction();
	}
}
