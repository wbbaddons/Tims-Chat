<?php
namespace chat\system\permission;
use \wcf\system\acl\ACLHandler;
use \wcf\system\package\PackageDependencyHandler;
use \wcf\system\WCF;

/**
 * Handles chat-permissions.
 *
 * @author 	Tim Düsterhus, Marcel Werk
 * @copyright	2010-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	be.bastelstu.chat
 * @subpackage	system.permission
 */
class PermissionHandler {
	/**
	 * permissions set for the active user
	 * @var array<boolean>
	 */
	protected $chatPermissions = array();
	
	/**
	 * given user decorated in a user profile
	 * @var \wcf\data\user\UserProfile
	 */
	protected $user = null;
	
	public function __construct(\wcf\data\user\User $user = null) {
		if ($user === null) $user = WCF::getUser();
		$this->user = new \wcf\data\user\UserProfile($user);
		
		$packageID = \chat\util\ChatUtil::getPackageID();
		$ush = \wcf\system\user\storage\UserStorageHandler::getInstance();
		
		// get groups permissions
		$this->chatPermissions = \chat\system\cache\builder\PermissionCacheBuilder::getInstance()->getData($user->getGroupIDs());
		
		// get user permissions
		if ($user->userID) {
			// get data from storage
			$ush->loadStorage(array($user->userID), $packageID);
			
			// get ids
			$data = $ush->getStorage(array($user->userID), 'chatUserPermissions', $packageID);
			
			// cache does not exist or is outdated
			if ($data[$user->userID] === null) {
				$userPermissions = array();
				
				$conditionBuilder = new \wcf\system\database\util\PreparedStatementConditionBuilder();
				$conditionBuilder->add('acl_option.objectTypeID = ?', array(ACLHandler::getInstance()->getObjectTypeID('be.bastelstu.chat.room')));
				$conditionBuilder->add('option_to_user.optionID = acl_option.optionID');
				$conditionBuilder->add('option_to_user.userID = ?', array($user->userID));
				$sql = "SELECT		option_to_user.objectID AS roomID, option_to_user.optionValue,
							acl_option.optionName AS permission
					FROM		wcf".WCF_N."_acl_option acl_option,
							wcf".WCF_N."_acl_option_to_user option_to_user
							".$conditionBuilder;
				$stmt = WCF::getDB()->prepareStatement($sql);
				$stmt->execute($conditionBuilder->getParameters());
				while ($row = $stmt->fetchArray()) {
					$userPermissions[$row['roomID']][$row['permission']] = $row['optionValue'];
				}
				
				// update cache
				$ush->update($user->userID, 'chatUserPermissions', serialize($userPermissions), $packageID);
			}
			else {
				$userPermissions = unserialize($data[$user->userID]);
			}
			
			foreach ($userPermissions as $roomID => $permissions) {
				foreach ($permissions as $name => $value) {
					$this->chatPermissions[$roomID][$name] = $value;
				}
			}
		}
	}
	
	/**
	 * Fetches the given permission for the given room
	 *
	 * @param	\chat\data\room\Room	$room
	 * @param	string			$permission
	 * @return	boolean
	 */
	public function getPermission(\chat\data\room\Room $room, $permission) {
		if (!isset($this->chatPermissions[$room->roomID][$permission])) {
			$permission = str_replace(array('user.', 'mod.'), array('user.chat.', 'mod.chat.'), $permission);
			
			return $this->user->getPermission($permission);
		}
		return (boolean) $this->chatPermissions[$room->roomID][$permission];
	}
	
	/**
	 * Clears the cache.
	 */
	public static function clearCache() {
		$packageID = \chat\util\ChatUtil::getPackageID();
		$ush = \wcf\system\user\storage\UserStorageHandler::getInstance();
		
		$ush->resetAll('chatUserPermissions', $packageID);
		\chat\system\cache\builder\PermissionCacheBuilder::getInstance()->reset();
	}
}
