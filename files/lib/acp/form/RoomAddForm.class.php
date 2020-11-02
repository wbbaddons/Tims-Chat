<?php
/*
 * Copyright (c) 2010-2018 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2024-11-02
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

namespace chat\acp\form;

use \chat\data\room\Room;
use \chat\data\room\RoomAction;
use \chat\data\room\RoomEditor;
use \wcf\system\acl\ACLHandler;
use \wcf\system\exception\UserInputException;
use \wcf\system\language\I18nHandler;
use \wcf\system\WCF;

/**
 * Shows the room add form.
 */
class RoomAddForm extends \wcf\form\AbstractForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'chat.acp.menu.link.room.add';

	/**
	 * @inheritDoc
	 */
	public $neededPermissions = [ 'admin.chat.canManageRoom' ];

	/**
	 * Object type ID of the ACL object type for rooms.
	 * @var int
	 */
	public $aclObjectTypeID = 0;

	/**
	 * Chat room title.
	 * @var string
	 */
	public $title = '';

	/**
	 * Chat room topic.
	 * @var string
	 */
	public $topic = '';

	/**
	 * Whether HTML should be interpreted in the room's topic.
	 * @var boolean
	 */
	public $topicUseHtml = false;

	/**
	 * Chat room user limit.
	 * @var int
	 */
	public $userLimit = 0;

	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();

		I18nHandler::getInstance()->register('title');
		I18nHandler::getInstance()->register('topic');

		$this->aclObjectTypeID = ACLHandler::getInstance()->getObjectTypeID('be.bastelstu.chat.room');
	}

	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();

		// read i18n values
		I18nHandler::getInstance()->readValues();

		// handle i18n plain input
		if (I18nHandler::getInstance()->isPlainValue('title')) $this->title = I18nHandler::getInstance()->getValue('title');
		if (I18nHandler::getInstance()->isPlainValue('topic')) $this->topic = I18nHandler::getInstance()->getValue('topic');
		if (isset($_POST['userLimit'])) $this->userLimit = intval($_POST['userLimit']);
		if (isset($_POST['topicUseHtml'])) $this->topicUseHtml = true;
	}

	/**
	 * @inheritDoc
	 */
	public function validate() {
		parent::validate();

		// validate title
		if (!I18nHandler::getInstance()->validateValue('title')) {
			if (I18nHandler::getInstance()->isPlainValue('title')) {
				throw new UserInputException('title');
			}
			else {
				throw new UserInputException('title', 'multilingual');
			}
		}

		// validate topic
		if (!I18nHandler::getInstance()->validateValue('topic', false, true)) {
			throw new UserInputException('topic');
		}

		if (mb_strlen($this->topic) > 10000) {
			throw new UserInputException('topic', 'tooLong');
		}

		if ($this->userLimit < 0) {
			throw new UserInputException('userLimit', 'negative');
		}
	}

	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();

		$fields = [ 'title' => $this->title
		          , 'topic' => $this->topic
		          , 'topicUseHtml' => (int) $this->topicUseHtml
		          , 'userLimit' => $this->userLimit
		          , 'position' => 0 // TODO
		          ];

		// create room
		$this->objectAction = new \chat\data\room\RoomAction([], 'create', [ 'data' => array_merge($this->additionalFields, $fields) ]);
		$returnValues = $this->objectAction->executeAction();

		// save i18n values
		$this->saveI18nValue($returnValues['returnValues'], [ 'title', 'topic' ]);

		// save ACL
		ACLHandler::getInstance()->save($returnValues['returnValues']->roomID, $this->aclObjectTypeID);

		$this->saved();

		// reset values
		$this->title = $this->topic = '';
		$this->userLimit = 0;
		$this->topicUseHtml = false;

		I18nHandler::getInstance()->reset();
		ACLHandler::getInstance()->disableAssignVariables();

		// show success message
		WCF::getTPL()->assign('success', true);
	}

	/**
	 * Saves i18n values.
	 *
	 * @param	Room      $room
	 * @param	string[]  $columns
	 */
	public function saveI18nValue(Room $room, $columns) {
		$data = [ ];

		foreach ($columns as $columnName) {
			$languageItem = 'chat.room.room'.$room->roomID.'.'.$columnName;

			if (I18nHandler::getInstance()->isPlainValue($columnName)) {
				if ($room->$columnName === $languageItem) {
					I18nHandler::getInstance()->remove($languageItem);
				}
			}
			else {
				$packageID = \wcf\data\package\PackageCache::getInstance()->getPackageID('be.bastelstu.chat');

				I18nHandler::getInstance()->save( $columnName
								, $languageItem
								, 'chat.room'
								, $packageID
								);

				$data[$columnName] = $languageItem;
			}
		}

		if (!empty($data)) {
			(new RoomEditor($room))->update($data);
		}
	}

	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();

		ACLHandler::getInstance()->assignVariables($this->aclObjectTypeID);
		I18nHandler::getInstance()->assignVariables();

		WCF::getTPL()->assign([ 'action' => 'add'
		                      , 'aclObjectTypeID' => $this->aclObjectTypeID
		                      , 'userLimit' => $this->userLimit
		                      , 'topicUseHtml' => $this->topicUseHtml
		                      ]);
	}
}

