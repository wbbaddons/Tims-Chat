<?php
namespace chat\acp\page;
use \wcf\system\WCF;

/**
 * Lists available chatrooms.
 * 
 * @author 	Tim Düsterhus
 * @copyright	2010-2014 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	acp.page
 */
class RoomListPage extends \wcf\page\AbstractPage {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'chat.acp.menu.link.room.list';
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array(
		'admin.chat.canEditRoom',
		'admin.chat.canDeleteRoom'
	);
	
	/**
	 * room list
	 * @var	\chat\data\room\RoomListPage
	 */
	public $objects = null;
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->objects = new \chat\data\room\RoomList();
		$this->objects->sqlOrderBy = 'showOrder ASC';
		$this->objects->getConditionBuilder()->add('permanent = ?', array(1));
		$this->objects->readObjects();
	}
	
	/**
	 * @see	wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'objects' => $this->objects
		));
	}
}
