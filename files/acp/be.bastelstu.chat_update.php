<?php

/*
 * Copyright (c) 2010-2024 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2028-06-15
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

use chat\data\message\MessageAction;
use wcf\data\object\type\ObjectTypeCache;

$objectTypeID = ObjectTypeCache::getInstance()->getObjectTypeIDByName(
    'be.bastelstu.chat.messageType',
    'be.bastelstu.chat.messageType.chatUpdate'
);

if ($objectTypeID) {
    (new MessageAction(
        [ ],
        'create',
        [
            'data' => [
                'roomID' => null,
                'userID' => null,
                'username' => '',
                'time' => TIME_NOW,
                'objectTypeID' => $objectTypeID,
                'payload' => \serialize([ ]),
            ],
        ]
    ))->executeAction();
}

$CHATCore = \file_get_contents(__DIR__ . '/../lib/system/CHATCore.class.php');
if (\strpos($CHATCore, 'chat.phar.php') === false) {
    @\unlink(__DIR__ . '/../chat.phar.php');
}
