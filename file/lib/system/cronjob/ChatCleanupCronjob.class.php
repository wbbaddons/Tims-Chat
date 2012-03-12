<?php
namespace wcf\system\cronjob;

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
		\wcf\data\chat\message\ChatMessageEditor::prune();
		\wcf\data\chat\room\ChatRoomEditor::prune();
	}
}
