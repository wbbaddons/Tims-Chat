<?php
namespace chat\system\cache\builder;
use wcf\system\WCF;

/**
 * Caches the chat permissions for a combination of user groups.
 * 
 * @author 	Tim Düsterhus, Marcel Werk
 * @copyright	2010-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	be.bastelstu.chat
 * @subpackage	system.cache.builder
 */
class PermissionCacheBuilder extends \wcf\system\cache\builder\AbstractCacheBuilder {
	/**
	 * @see wcf\system\cache\AbstractCacheBuilder::rebuild()
	 */
	public function rebuild(array $parameters) {
		$data = array();
		
		if (!empty($parameters)) {
			$conditionBuilder = new \wcf\system\database\util\PreparedStatementConditionBuilder();
			$conditionBuilder->add('acl_option.objectTypeID = ?', array(\wcf\system\acl\ACLHandler::getInstance()->getObjectTypeID('be.bastelstu.chat.room')));
			$conditionBuilder->add('option_to_group.optionID = acl_option.optionID');
			$conditionBuilder->add('option_to_group.groupID IN (?)', array($parameters));
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
