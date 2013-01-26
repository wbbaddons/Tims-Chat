<?php
namespace chat\data;

abstract class CHATDatabaseObject extends \wcf\data\DatabaseObject {
	/**
	 * @see \wcf\data\DatabaseObject::getDatabaseTableName()
	 */
	public static function getDatabaseTableName() {
	    return 'chat'.WCF_N.'_'.static::$databaseTableName;
	}
}