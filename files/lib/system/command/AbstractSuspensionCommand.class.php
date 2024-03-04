<?php

/*
 * Copyright (c) 2010-2024 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2028-03-14
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
use chat\data\user\User as ChatUser;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\UserProfile;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * Represents a command that creates suspensions
 */
abstract class AbstractSuspensionCommand extends AbstractCommand
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
        $this->assertParameter($parameters, 'duration');
        $this->assertParameter($parameters, 'reason');

        $this->assertUser($parameters['username']);
        if ($parameters['duration'] !== null && $parameters['duration'] < TIME_NOW) {
            throw new UserInputException(
                'message',
                WCF::getLanguage()->getDynamicVariable('chat.error.datePast')
            );
        }
        if (!empty($parameters['reason']) && \mb_strlen($parameters['reason']) > 100) {
            throw new UserInputException(
                'message',
                WCF::getLanguage()->getDynamicVariable(
                    'wcf.message.error.tooLong',
                    [
                        'maxTextLength' => 250,
                    ]
                )
            );
        }
        $this->checkPermissions($parameters, $room, $user);

        $test = new Suspension(null, $this->getSuspensionData($parameters, $room, $user));
        if (!$test->isActive()) {
            throw new UserInputException(
                'message',
                WCF::getLanguage()->getDynamicVariable('chat.error.suspension.noEffect')
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

        $data = $this->getSuspensionData($parameters, $room, $user);
        $test = new Suspension(null, $data);
        if (!$test->isActive()) {
            return;
        }

        WCF::getDB()->beginTransaction();
        $suspension = (new SuspensionAction(
            [ ],
            'create',
            [
                'data' => $data,
            ]
        ))->executeAction()['returnValues'];

        $this->afterCreate($suspension, $parameters, $room, $user);
        WCF::getDB()->commitTransaction();
    }

    /**
     * Creates chat messages informing about the suspension.
     *
     * @param  mixed[]                         $parameters
     */
    protected function afterCreate(Suspension $suspension, $parameters, Room $room, UserProfile $user)
    {
        $objectTypeID = $this->getMessageObjectTypeID('be.bastelstu.chat.messageType.suspend');
        $target = $suspension->getUser();

        if ($suspension->getRoom() === null) {
            $roomIDs = \array_map(static function (Room $room) {
                return $room->roomID;
            }, (new ChatUser($target))->getRooms());
            $roomIDs[] = $room->roomID;
        } else {
            $roomIDs = [ $suspension->getRoom()->roomID ];
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
                        'suspension' => $suspension,
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
     * Returns the database fields.
     *
     * @param  mixed[]                    $parameters
     * @return mixed[]
     */
    protected function getSuspensionData($parameters, Room $room, ?UserProfile $user = null)
    {
        $target = $this->getUser($parameters['username']);
        $globally = $this->isGlobally($parameters);
        $expires = $parameters['duration'];
        $reason = $parameters['reason'] ?: '';

        $roomID = $globally ? null : $room->roomID;
        $objectTypeID = ObjectTypeCache::getInstance()->getObjectTypeIDByName(
            'be.bastelstu.chat.suspension',
            $this->getObjectTypeName()
        );

        return [
            'time' => TIME_NOW,
            'expires' => $expires,
            'roomID' => $roomID,
            'userID' => $target->userID,
            'objectTypeID' => $objectTypeID,
            'reason' => $reason,
            'judgeID' => $user->userID,
            'judge' => $user->username,
        ];
    }

    /**
     * Returns whether a global suspension was requested.
     *
     * @param  mixed[] $parameters
     * @return boolean
     */
    protected function isGlobally($parameters)
    {
        return $parameters['globally'] === true;
    }
}
