<?php
namespace chat\data\message;

/**
 * Represents a viewable chat message.
 * 
 * @author	Tim Düsterhus
 * @copyright	2010-2014 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 * @subpackage	data.message
 */
class ViewableMessage extends \wcf\data\DatabaseObjectDecorator {
	/**
	 * @see	\wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'chat\data\message\Message';
	
	/**
	 * user profile object
	 * @var \wcf\data\user\UserProfile
	 */
	protected $userProfile = null;
	
	/**
	 * Returns the profile object of the user who created the post.
	 * 
	 * @return	wcf\data\user\UserProfile
	 */
	public function getUserProfile() {
		if ($this->userProfile === null) {
			$this->userProfile = new \wcf\data\user\UserProfile(new \wcf\data\user\User(null, $this->getDecoratedObject()->data));
		}
		
		return $this->userProfile;
	}
	
	/**
	 * @see	\chat\data\message\Message::jsonify()
	 */
	public function jsonify($raw = false) {
		$array = parent::jsonify(true);
		
		$array['avatar'] = array(
			16 => $this->getUserProfile()->getAvatar()->getImageTag(16),
			24 => $this->getUserProfile()->getAvatar()->getImageTag(24),
			32 => $this->getUserProfile()->getAvatar()->getImageTag(32),
			48 => $this->getUserProfile()->getAvatar()->getImageTag(48)
		);
	
		if ($raw) return $array;
		return \wcf\util\JSON::encode($array);
	}
}
