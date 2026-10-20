<?php

/*
 * Copyright (c) 2010-2026 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2030-10-20
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

// 1) Generate a blueprint to fill in the generated index names.

use wcf\system\database\table\index\DatabaseTableIndex;
use wcf\system\database\table\PartialDatabaseTable;

$blueprint = [
    PartialDatabaseTable::create('chat1_room')
        ->indices([
            // DatabaseTablePrimaryIndex::create()
            //     ->columns(['roomID']),
        ]),

    PartialDatabaseTable::create('chat1_room_to_user')
        ->indices([
            // DatabaseTablePrimaryIndex::create()
            //     ->columns(['roomID', 'userID']),
            DatabaseTableIndex::create('')
                ->columns(['userID']),
            DatabaseTableIndex::create('')
                ->columns(['roomID', 'active']),
            DatabaseTableIndex::create('')
                ->columns(['active']),
        ]),

    PartialDatabaseTable::create('chat1_message')
        ->indices([
            // DatabaseTablePrimaryIndex::create()
            //     ->columns(['messageID']),
            DatabaseTableIndex::create('')
                ->columns(['roomID']),
            DatabaseTableIndex::create('')
                ->columns(['userID']),
            DatabaseTableIndex::create('')
                ->columns(['time']),
        ]),

    PartialDatabaseTable::create('chat1_command')
        ->indices([
            // DatabaseTablePrimaryIndex::create()
            //     ->columns(['commandID']),
            DatabaseTableIndex::create('')
                ->type(DatabaseTableIndex::UNIQUE_TYPE)
                ->columns(['packageID', 'identifier']),
            DatabaseTableIndex::create('')
                ->type(DatabaseTableIndex::UNIQUE_TYPE)
                ->columns(['className']),
        ]),

    PartialDatabaseTable::create('chat1_command_trigger')
        ->indices([
            // DatabaseTablePrimaryIndex::create()
            //     ->columns(['triggerID']),
            DatabaseTableIndex::create('')
                ->type(DatabaseTableIndex::UNIQUE_TYPE)
                ->columns(['commandTrigger']),
            DatabaseTableIndex::create('')
                ->columns(['commandID']),
        ]),

    PartialDatabaseTable::create('chat1_session')
        ->indices([
            // DatabaseTablePrimaryIndex::create()
            //     ->columns(['roomID', 'userID', 'sessionID']),
            DatabaseTableIndex::create('')
                ->columns(['userID', 'sessionID']),
            DatabaseTableIndex::create('')
                ->columns(['sessionID']),
            DatabaseTableIndex::create('')
                ->columns(['lastRequest']),
        ]),

    PartialDatabaseTable::create('chat1_suspension')
        ->indices([
            // DatabaseTablePrimaryIndex::create()
            //     ->columns(['suspensionID']),
            DatabaseTableIndex::create('')
                ->columns(['roomID', 'userID', 'objectTypeID']),
            DatabaseTableIndex::create('')
                ->columns(['userID']),
            DatabaseTableIndex::create('')
                ->columns(['objectTypeID', 'roomID']),
            DatabaseTableIndex::create('')
                ->columns(['time']),
            DatabaseTableIndex::create('')
                ->columns(['judgeID']),
        ]),

    PartialDatabaseTable::create('chat1_room_temporary_invite')
        ->indices([
            // DatabaseTablePrimaryIndex::create()
            //     ->columns(['roomID', 'userID']),
            DatabaseTableIndex::create('')
                ->columns(['userID']),
        ]),
];

// 2) Use this blueprint to recreate the index objects with ->generatedName() set to false.
// Simply dropping the indices with ->generatedName() set to true does not work, because it will
// also remove named indices as the fact that a name was generated does not persist to the database.

$data = [];
foreach ($blueprint as $blueprintTable) {
    $data[] = PartialDatabaseTable::create($blueprintTable->getName())
        ->indices(\array_map(static function ($index) {
            \assert($index instanceof DatabaseTableIndex);

            return DatabaseTableIndex::create($index->getName())
                ->columns($index->getColumns())
                ->type($index->getType())
                ->drop();
        }, $blueprintTable->getIndices()));
}

return $data;
