<?php
namespace chat\data\message;

/**
 * Represents a list of viewable chat messages.
 * 
 * @author 	Tim Düsterhus
 * @copyright	2010-2014 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	chat.room
 */
class ViewableMessageList extends MessageList {
	/**
	 * @see	\wcf\data\DatabaseObjectList::$decoratorClassName
	 */
	public $decoratorClassName = 'chat\data\message\ViewableMessage';
	
	/**
	 * @see \wcf\data\DatabaseObjectList::__construct()
	 */
	public function __construct() {
		parent::__construct();
		
		$this->sqlSelects .= "user_avatar.*, user_option_value.*, user_table.*";
		$this->sqlJoins .= " LEFT JOIN wcf".WCF_N."_user user_table ON (user_table.userID = message.sender)";
		$this->sqlJoins .= " LEFT JOIN wcf".WCF_N."_user_avatar user_avatar ON (user_avatar.avatarID = user_table.avatarID)";
		$this->sqlJoins .= " LEFT JOIN wcf".WCF_N."_user_option_value user_option_value ON (user_option_value.userID = user_table.userID)";
		
		if (MODULE_USER_RANK) {
			$this->sqlSelects .= ",user_rank.*";
			$this->sqlJoins .= " LEFT JOIN wcf".WCF_N."_user_rank user_rank ON (user_rank.rankID = user_table.rankID)";
		}
	}
}
