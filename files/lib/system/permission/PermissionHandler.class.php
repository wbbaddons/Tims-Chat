<?php

/**
 * Copyright (C) 2010-2024  Tim Düsterhus
 * Copyright (C) 2010-2024  Woltlab GmbH
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

namespace chat\system\permission;

use chat\data\room\Room;
use chat\system\cache\builder\PermissionCacheBuilder;
use wcf\data\user\UserProfile;
use wcf\system\acl\ACLHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;

/**
 * Handles chat permissions.
 */
class PermissionHandler
{
    /**
     * permissions set for the given user
     * @var boolean[]
     */
    protected $chatPermissions = [ ];

    /**
     * given user decorated in a user profile
     * @var \wcf\data\user\UserProfile
     */
    protected $user;

    /**
     * Cache of PermissionHandlers.
     * @var \chat\system\permission\PermissionHandler[]
     */
    protected static $cache = [ ];

    public function __construct(?UserProfile $user = null)
    {
        if ($user === null) {
            $user = new UserProfile(WCF::getUser());
        }
        $this->user = $user;

        $this->chatPermissions = PermissionCacheBuilder::getInstance()->getData($user->getGroupIDs());

        // get user permissions
        if ($user->userID) {
            $ush = UserStorageHandler::getInstance();

            // get ids
            $data = $ush->getField('chatUserPermissions', $user->userID);

            // cache does not exist or is outdated
            if ($data === null) {
                $userPermissions = [ ];

                $conditionBuilder = new PreparedStatementConditionBuilder();
                $conditionBuilder->add('acl_option.objectTypeID = ?', [ ACLHandler::getInstance()->getObjectTypeID('be.bastelstu.chat.room') ]);
                $conditionBuilder->add('option_to_user.userID = ?', [ $user->userID ]);
                $sql = "SELECT     option_to_user.objectID AS roomID,
                                   option_to_user.optionValue,
                                   acl_option.optionName AS permission
                        FROM       wcf1_acl_option acl_option
                        INNER JOIN wcf1_acl_option_to_user option_to_user
                                   ON option_to_user.optionID = acl_option.optionID
                        " . $conditionBuilder;
                $statement = WCF::getDB()->prepare($sql);
                $statement->execute($conditionBuilder->getParameters());
                while (($row = $statement->fetchArray())) {
                    $userPermissions[$row['roomID']][$row['permission']] = $row['optionValue'];
                }

                // update cache
                $ush->update($user->userID, 'chatUserPermissions', \serialize($userPermissions));
            } else {
                $userPermissions = \unserialize($data);
            }

            foreach ($userPermissions as $roomID => $permissions) {
                foreach ($permissions as $name => $value) {
                    $this->chatPermissions[$roomID][$name] = $value;
                }
            }
        }
    }

    public static function get(?UserProfile $user = null)
    {
        if ($user === null) {
            $user = new UserProfile(WCF::getUser());
        }
        if (!isset(static::$cache[$user->userID])) {
            static::$cache[$user->userID] = new static($user);
        }

        return static::$cache[$user->userID];
    }

    /**
     * Fetches the given permission for the given room
     *
     * @param   string          $permission
     * @return  boolean
     */
    public function getPermission(Room $room, $permission)
    {
        $groupPermission = \str_replace(
            [
                'user.',
                'mod.',
            ],
            [
                'user.chat.',
                'mod.chat.',
            ],
            $permission
        );

        if (\method_exists($this->user, 'getNeverPermission') && $this->user->getNeverPermission($groupPermission)) {
            return false;
        }

        if (!isset($this->chatPermissions[$room->roomID][$permission])) {
            return $this->user->getPermission($groupPermission);
        }

        return (bool)$this->chatPermissions[$room->roomID][$permission];
    }

    /**
     * Clears the cache.
     */
    public static function resetCache()
    {
        UserStorageHandler::getInstance()->resetAll('chatUserPermissions');
        PermissionCacheBuilder::getInstance()->reset();
    }
}
