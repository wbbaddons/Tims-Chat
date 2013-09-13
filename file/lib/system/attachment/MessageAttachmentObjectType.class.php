<?php
namespace chat\system\attachment;

/**
 * Attachment object type implementation for chat messages.
 *
 * @author 	Maximilian Mader
 * @copyright	2010-2013 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	system.attachment
 */
class MessageAttachmentObjectType extends AbstractAttachmentObjectType {
	/**
	 * @see	wcf\system\attachment\IAttachmentObjectType::getMaxSize()
	 */
	public function getMaxSize() {
		return WCF::getSession()->getPermission('user.chat.maxAttachmentSize');
	}
	
	/**
	 * @see	wcf\system\attachment\IAttachmentObjectType::getAllowedExtensions()
	 */
	public function getAllowedExtensions() {
		return ArrayUtil::trim(explode("\n", WCF::getSession()->getPermission('user.chat.allowedAttachmentExtensions')));
	}
	
	/**
	 * @see	wcf\system\attachment\IAttachmentObjectType::getMaxCount()
	 */
	public function getMaxCount() {
		return 1;
	}
	
	/**
	 * @see	wcf\system\attachment\IAttachmentObjectType::canDownload()
	 */
	public function canDownload($objectID) {
		if ($objectID) {
			$room = \chat\data\room\RoomCache::getInstance()->getRoom($objectID);
			if ($room && $room->canEnter()) return true;
		}
		
		return false;
	}
	
	/**
	 * @see	wcf\system\attachment\IAttachmentObjectType::canUpload()
	 */
	public function canUpload($objectID, $parentObjectID = 0) {
		if ($objectID) {
			$room = \chat\data\room\RoomCache::getInstance()->getRoom($objectID);
			if ($room && $room->canWrite()) return true;
		}
		
		return WCF::getSession()->getPermission('user.chat.canUploadAttachment');
	}
	
	/**
	 * @see	wcf\system\attachment\IAttachmentObjectType::canDelete()
	 */
	public function canDelete($objectID) {
		return false;
	}
}
