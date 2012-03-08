<?php
namespace wcf\system\cache\builder;
use wcf\system\WCF;

/**
 * Caches the chat permissions for a combination of user groups.
 * 
 * @author 	Tim Düsterhus, Marcel Werk
 * @copyright	2010-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	timwolla.wcf.chat
 * @subpackage	system.cache.builder
 */
class ChatPermissionCacheBuilder implements ICacheBuilder {
	/**
	 * @see wcf\system\cache\ICacheBuilder::getData()
	 */
	public function getData(array $cacheResource) {
		$data = array();
		list(, $groupIDsStr) = explode('-', $cacheResource['cache']);
		$groupIDs = explode(',', $groupIDsStr);
		
		if (count($groupIDs)) {
			$conditionBuilder = new \wcf\system\database\util\PreparedStatementConditionBuilder();
			$conditionBuilder->add('acl_option.packageID IN (?)', array(\wcf\system\package\PackageDependencyHandler::getInstance()->getDependencies()));
			$conditionBuilder->add('acl_option.objectTypeID = ?', array(\wcf\system\acl\ACLHandler::getInstance()->getObjectTypeID('timwolla.wcf.chat.room')));
			$conditionBuilder->add('option_to_group.optionID = acl_option.optionID');
			$conditionBuilder->add('option_to_group.groupID IN (?)', array($groupIDs));
			$sql = "SELECT		option_to_group.groupID, option_to_group.objectID AS roomID, option_to_group.optionValue,
						acl_option.optionName AS permission
				FROM		wcf".WCF_N."_acl_option acl_option,
						wcf".WCF_N."_acl_option_to_group option_to_group
						".$conditionBuilder;
			$stmt = WCF::getDB()->prepareStatement($sql);
			$stmt->execute($conditionBuilder->getParameters());
			while ($row = $stmt->fetchArray()) {
				if (!isset($data[$row['roomID']][$row['permission']])) $data[$row['roomID']][$row['permission']] = $row['optionValue'];
				else $data[$row['roomID']][$row['permission']] = $row['optionValue'] || $data[$row['roomID']][$row['permission']];
			}
		}
		
		return $data;
	}
}