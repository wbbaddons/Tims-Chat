<?php

/*
 * Copyright (c) 2010-2022 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2027-02-22
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

namespace chat\system\command;

use chat\data\message\MessageAction;
use chat\data\room\Room;
use chat\data\user\User as ChatUser;
use wcf\data\user\UserEditor;
use wcf\data\user\UserProfile;
use wcf\system\exception\UserInputException;
use wcf\system\message\censorship\Censorship;
use wcf\system\WCF;

/**
 * The away command marks the user as being away.
 */
final class AwayCommand extends AbstractCommand implements ICommand
{
    /**
     * @inheritDoc
     */
    public function getJavaScriptModuleName()
    {
        return 'Bastelstu.be/Chat/Command/Away';
    }

    /**
     * @inheritDoc
     */
    public function validate($parameters, Room $room, ?UserProfile $user = null)
    {
        if ($user === null) {
            $user = new UserProfile(WCF::getUser());
        }

        $reason = $this->assertParameter($parameters, 'reason');

        // search for censored words
        if (ENABLE_CENSORSHIP) {
            $result = Censorship::getInstance()->test($reason);
            if ($result) {
                throw new UserInputException(
                    'message',
                    WCF::getLanguage()->getDynamicVariable(
                        'wcf.message.error.censoredWordsFound',
                        [
                            'censoredWords' => $result,
                        ]
                    )
                );
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function execute($parameters, Room $room, ?UserProfile $user = null)
    {
        if ($user === null) {
            $user = new UserProfile(WCF::getUser());
        }

        $reason = $this->assertParameter($parameters, 'reason');

        $objectTypeID = $this->getMessageObjectTypeID('be.bastelstu.chat.messageType.away');
        $rooms = \array_map(static function (Room $room) use ($user) {
            return [
                'roomID' => $room->roomID,
                'isSilent' => !$room->canWritePublicly($user),
            ];
        }, (new ChatUser($user->getDecoratedObject()))->getRooms());

        WCF::getDB()->beginTransaction();
        $editor = new UserEditor($user->getDecoratedObject());
        $editor->update([
            'chatAway' => $reason,
        ]);

        (new MessageAction(
            [ ],
            'create',
            [
                'data' => [
                    'roomID' => $room->roomID,
                    'userID' => $user->userID,
                    'username' => $user->username,
                    'time' => TIME_NOW,
                    'objectTypeID' => $objectTypeID,
                    'payload' => \serialize([
                        'message' => $reason,
                        'rooms' => \array_values($rooms),
                    ]),
                ],
                'updateTimestamp' => true,
            ]
        ))->executeAction();
        WCF::getDB()->commitTransaction();
    }
}
