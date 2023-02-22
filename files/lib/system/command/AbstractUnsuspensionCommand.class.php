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
 * License, use of this software will be governed by version 3
 * or later of the General Public License.
 */

namespace chat\system\command;

use chat\data\message\MessageAction;
use chat\data\room\Room;
use chat\data\suspension\Suspension;
use chat\data\suspension\SuspensionAction;
use chat\data\suspension\SuspensionList;
use chat\data\user\User as ChatUser;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\UserProfile;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * Represents a command that revokes suspensions
 */
abstract class AbstractUnsuspensionCommand extends AbstractCommand
{
    use TNeedsUser;

    /**
     * Returns the name of the object type for this suspension.
     *
     * @return string
     */
    abstract public function getObjectTypeName();

    /**
     * Checks the permissions to execute this command.
     * Throws if necessary.
     *
     * @see \chat\system\command\ICommand::validate()
     */
    abstract protected function checkPermissions($parameters, Room $room, UserProfile $user);

    /**
     * @inheritDoc
     */
    public function validate($parameters, Room $room, ?UserProfile $user = null)
    {
        if ($user === null) {
            $user = new UserProfile(WCF::getUser());
        }

        $this->assertParameter($parameters, 'username');
        $this->assertParameter($parameters, 'globally');

        $this->assertUser($parameters['username']);

        $suspensions = $this->getSuspensionData($parameters, $room, $user);
        if (empty($suspensions)) {
            throw new UserInputException(
                'message',
                WCF::getLanguage()->getDynamicVariable('chat.error.suspension.remove.empty')
            );
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

        $suspensions = $this->getSuspensionData($parameters, $room, $user);

        WCF::getDB()->beginTransaction();
        (new SuspensionAction(
            $suspensions,
            'revoke',
            [ ]
        ))->executeAction();
        $this->afterCreate($suspensions, $parameters, $room, $user);
        WCF::getDB()->commitTransaction();
    }

    /**
     * Creates chat messages informing about the removed suspensions.
     *
     * @param \chat\data\suspension\Suspension[] $suspension
     * @param  mixed[]                           $parameters
     */
    protected function afterCreate($suspensions, $parameters, Room $room, UserProfile $user)
    {
        $objectTypeID = $this->getMessageObjectTypeID('be.bastelstu.chat.messageType.unsuspend');
        $target = $this->getUser($parameters['username']);
        if ($this->isGlobally($parameters)) {
            $roomIDs = \array_map(static function (Room $room) {
                return $room->roomID;
            }, (new ChatUser($target))->getRooms());
            $roomIDs[] = $room->roomID;
        } else {
            $roomIDs = [
                $room->roomID,
            ];
        }

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
                        'objectType' => $this->getObjectTypeName(),
                        'roomIDs' => $roomIDs,
                        'globally' => $this->isGlobally($parameters),
                        'target' => [
                            'userID' => $target->userID,
                            'username' => $target->username,
                        ],
                    ]),
                ],
                'updateTimestamp' => true,
            ]
        ))->executeAction();
    }

    /**
     * Returns the active suspensions.
     *
     * @param  mixed[]                    $parameters
     * @return mixed[]
     */
    protected function getSuspensionData($parameters, Room $room, ?UserProfile $user = null)
    {
        $target = $this->getUser($parameters['username']);

        $roomID = $this->isGlobally($parameters) ? null : $room->roomID;
        $objectTypeID = ObjectTypeCache::getInstance()->getObjectTypeIDByName(
            'be.bastelstu.chat.suspension',
            $this->getObjectTypeName()
        );

        $suspensionList = new SuspensionList();

        $suspensionList->getConditionBuilder()->add('(expires IS NULL OR expires > ?)', [ TIME_NOW ]);
        $suspensionList->getConditionBuilder()->add('revoked IS NULL');
        $suspensionList->getConditionBuilder()->add('userID = ?', [ $target->userID ]);
        $suspensionList->getConditionBuilder()->add('objectTypeID = ?', [ $objectTypeID ]);
        if ($roomID === null) {
            $suspensionList->getConditionBuilder()->add('roomID IS NULL');
        } else {
            $suspensionList->getConditionBuilder()->add('roomID = ?', [ $room->roomID ]);
        }

        $suspensionList->readObjects();

        return \array_filter($suspensionList->getObjects(), static function (Suspension $suspension) {
            return $suspension->isActive();
        });
    }

    /**
     * Returns whether a global suspension removal was requested.
     *
     * @param  mixed[] $parameters
     * @return boolean
     */
    protected function isGlobally($parameters)
    {
        return $parameters['globally'] === true;
    }
}
