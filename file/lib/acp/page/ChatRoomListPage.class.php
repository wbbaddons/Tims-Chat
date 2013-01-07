<?php
namespace wcf\acp\page;

/**
 * Lists available chatrooms.
 * 
 * @author 	Tim Düsterhus
 * @copyright	2010-2012 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.wcf.chat
 * @subpackage	acp.page
 */
class ChatRoomListPage extends \wcf\page\MultipleLinkPage {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.chat.room.list';
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array(
		'admin.content.chat.canEditRoom',
		'admin.content.chat.canDeleteRoom'
	);
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::$objectListClassName
	 */
	public $objectListClassName = '\wcf\data\chat\room\ChatRoomList';
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::$sortField
	 */
	public $sortField = 'position';
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::$sortOrder
	 */
	public $sortOrder = 'ASC';
}
