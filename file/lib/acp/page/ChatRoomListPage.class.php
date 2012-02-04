<?php
namespace wcf\acp\page;

/**
 * Lists available chatrooms.
 * 
 * @author	Tim DÃ¼sterhus
 * @copyright	2011-2012 Tim DÃ¼sterhus
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.bbcode
 * @subpackage	acp.page
 * @category 	Community Framework
 */
class ChatRoomListPage extends \wcf\page\MultipleLinkPage {
	/**
	 * @see wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array(
		'admin.content.chat.canEditRoom',
		'admin.content.chat.canDeleteRoom'
	);
	
	/**
	 * @see	wcf\page\MultipleLinkPage::$objectListClassName
	 */
	public $objectListClassName = '\wcf\data\chat\room\ChatRoomList';
	
	/**
	 * @see	wcf\page\MultipleLinkPage::$sortField
	 */
	public $sortField = 'position';
	
	/**
	 * @see	wcf\page\MultipleLinkPage::$sortOrder
	 */
	public $sortOrder = 'ASC';
	
	/**
	 * @see wcf\page\IPage::show()
	 */
	public function show() {
		// set active menu item.
		\wcf\system\menu\acp\ACPMenu::getInstance()->setActiveMenuItem('wcf.acp.menu.link.chat.room.list');
		
		parent::show();
	}
}
