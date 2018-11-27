<?php
/*
 * Copyright (c) 2010-2018 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2022-11-27
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

namespace chat\system\command;

use \chat\data\message\MessageAction;
use \chat\data\room\Room;
use \wcf\data\user\UserProfile;
use \wcf\system\exception\PermissionDeniedException;
use \wcf\system\exception\UserInputException;
use \wcf\system\message\censorship\Censorship;
use \wcf\system\WCF;

/**
 * MeCommand represents an action message.
 */
class MeCommand extends AbstractCommand implements ICommand {
	/**
	 * @inheritDoc
	 */
	public function getJavaScriptModuleName() {
		return 'Bastelstu.be/Chat/Command/Me';
	}

	/**
	 * @inheritDoc
	 */
	public function validate($parameters, Room $room, UserProfile $user = null) {
		if ($user === null) $user = new UserProfile(\wcf\system\WCF::getUser());

		if (!$room->canWritePublicly($user)) throw new PermissionDeniedException();

		$text = $this->assertParameter($parameters, 'text');

		if (mb_strlen($text) === 0) {
			throw new UserInputException('message', WCF::getLanguage()->getDynamicVariable('wcf.global.form.error.empty'));
		}

		// validate message length
		if (mb_strlen($text) > CHAT_MAX_LENGTH) {
			throw new UserInputException('message', WCF::getLanguage()->getDynamicVariable('wcf.message.error.tooLong', [ 'maxTextLength' => CHAT_MAX_LENGTH ]));
		}

		// search for censored words
		if (ENABLE_CENSORSHIP) {
			$result = Censorship::getInstance()->test($text);
			if ($result) {
				throw new UserInputException('message', WCF::getLanguage()->getDynamicVariable('wcf.message.error.censoredWordsFound', [ 'censoredWords' => $result ]));
			}
		}
	}

	/**
	 * @inheritDoc
	 */
	public function execute($parameters, Room $room, UserProfile $user = null) {
		if ($user === null) $user = new UserProfile(\wcf\system\WCF::getUser());

		$objectTypeID = $this->getMessageObjectTypeID('be.bastelstu.chat.messageType.me');
		(new MessageAction([ ], 'create', [ 'data' => [ 'roomID'       => $room->roomID
		                                              , 'userID'       => $user->userID
		                                              , 'username'     => $user->username
		                                              , 'time'         => TIME_NOW
		                                              , 'objectTypeID' => $objectTypeID
		                                              , 'payload'      => serialize([ 'message' => $this->assertParameter($parameters, 'text') ])
		                                              ]
		                                  , 'updateTimestamp' => true
		                                  , 'grantPoints' => true
		                                  ]
		                  )
		)->executeAction();
	}
}
