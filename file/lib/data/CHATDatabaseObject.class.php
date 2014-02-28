<?php
namespace chat\data;

/**
 * Basic implementation that sets proper table name.
 * 
 * @author	Tim Düsterhus
 * @copyright	2010-2014 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	data
 */
abstract class CHATDatabaseObject extends \wcf\data\DatabaseObject {
	/**
	 * @see \wcf\data\DatabaseObject::getDatabaseTableName()
	 */
	public static function getDatabaseTableName() {
		return 'chat'.WCF_N.'_'.static::$databaseTableName;
	}
}
