<?php
/**
 * Copyright (C) 2010-2021  Tim Düsterhus
 * Copyright (C) 2010-2021  Woltlab GmbH
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 */

namespace chat\system\cache\builder;

use \wcf\system\acl\ACLHandler;
use \wcf\system\WCF;

/**
 * Caches the chat permissions for a combination of user groups.
 */
class PermissionCacheBuilder extends \wcf\system\cache\builder\AbstractCacheBuilder {
	/**
	 * @inheritDoc
	 */
	public function rebuild(array $parameters) {
		$data = [ ];

		if (!empty($parameters)) {
			$conditionBuilder = new \wcf\system\database\util\PreparedStatementConditionBuilder();
			$conditionBuilder->add('acl_option.objectTypeID = ?', [ ACLHandler::getInstance()->getObjectTypeID('be.bastelstu.chat.room') ]);
			$conditionBuilder->add('option_to_group.groupID IN (?)', [ $parameters ]);
			$sql = "SELECT     option_to_group.objectID AS roomID,
				           option_to_group.optionValue,
				           acl_option.optionName AS permission
				FROM       wcf".WCF_N."_acl_option acl_option
				INNER JOIN wcf".WCF_N."_acl_option_to_group option_to_group
				        ON option_to_group.optionID = acl_option.optionID
				".$conditionBuilder;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditionBuilder->getParameters());
			while (($row = $statement->fetchArray())) {
				if (!isset($data[$row['roomID']][$row['permission']])) {
					$data[$row['roomID']][$row['permission']] = $row['optionValue'];
				}
				else {
					$data[$row['roomID']][$row['permission']] = $row['optionValue'] || $data[$row['roomID']][$row['permission']];
				}
			}
		}

		return $data;
	}
}
