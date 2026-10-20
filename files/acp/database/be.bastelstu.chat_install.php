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

use wcf\system\database\table\column\BinaryDatabaseTableColumn;
use wcf\system\database\table\column\DefaultFalseBooleanDatabaseTableColumn;
use wcf\system\database\table\column\IntDatabaseTableColumn;
use wcf\system\database\table\column\MediumblobDatabaseTableColumn;
use wcf\system\database\table\column\NotNullInt10DatabaseTableColumn;
use wcf\system\database\table\column\NotNullVarchar191DatabaseTableColumn;
use wcf\system\database\table\column\NotNullVarchar255DatabaseTableColumn;
use wcf\system\database\table\column\ObjectIdDatabaseTableColumn;
use wcf\system\database\table\column\SmallintDatabaseTableColumn;
use wcf\system\database\table\column\TextDatabaseTableColumn;
use wcf\system\database\table\column\VarcharDatabaseTableColumn;
use wcf\system\database\table\DatabaseTable;
use wcf\system\database\table\index\DatabaseTableForeignKey;
use wcf\system\database\table\index\DatabaseTableIndex;
use wcf\system\database\table\index\DatabaseTablePrimaryIndex;
use wcf\system\database\table\PartialDatabaseTable;

return [
    PartialDatabaseTable::create('wcf1_user')
        ->columns([
            TextDatabaseTableColumn::create('chatAway'),
            IntDatabaseTableColumn::create('chatColor1')
                ->length(10),
            IntDatabaseTableColumn::create('chatColor2')
                ->length(10),
        ]),

    DatabaseTable::create('chat1_room')
        ->columns([
            ObjectIdDatabaseTableColumn::create('roomID'),
            NotNullVarchar255DatabaseTableColumn::create('title'),
            TextDatabaseTableColumn::create('topic')
                ->notNull(),
            SmallintDatabaseTableColumn::create('position')
                ->length(5)
                ->notNull(),
            NotNullInt10DatabaseTableColumn::create('userLimit')
                ->defaultValue(0),
            DefaultFalseBooleanDatabaseTableColumn::create('isTemporary'),
            IntDatabaseTableColumn::create('ownerID')
                ->length(10),
            DefaultFalseBooleanDatabaseTableColumn::create('topicUseHtml'),
        ])
        ->indices([
            DatabaseTablePrimaryIndex::create()
                ->columns(['roomID']),
        ])
        ->foreignKeys([
            DatabaseTableForeignKey::create()
                ->columns(['ownerID'])
                ->referencedTable('wcf1_user')
                ->referencedColumns(['userID'])
                ->onDelete('SET NULL'),
        ]),

    DatabaseTable::create('chat1_room_to_user')
        ->columns([
            NotNullInt10DatabaseTableColumn::create('roomID'),
            NotNullInt10DatabaseTableColumn::create('userID'),
            NotNullInt10DatabaseTableColumn::create('lastPull')
                ->defaultValue(0),
            NotNullInt10DatabaseTableColumn::create('lastPush')
                ->defaultValue(0),
            DefaultFalseBooleanDatabaseTableColumn::create('active'),
        ])
        ->indices([
            DatabaseTablePrimaryIndex::create()
                ->columns(['roomID', 'userID']),
            DatabaseTableIndex::create('userID')
                ->columns(['userID']),
            DatabaseTableIndex::create('roomID')
                ->columns(['roomID', 'active']),
            DatabaseTableIndex::create('active')
                ->columns(['active']),
        ])
        ->foreignKeys([
            DatabaseTableForeignKey::create()
                ->columns(['roomID'])
                ->referencedTable('chat1_room')
                ->referencedColumns(['roomID'])
                ->onDelete('CASCADE'),
            DatabaseTableForeignKey::create()
                ->columns(['userID'])
                ->referencedTable('wcf1_user')
                ->referencedColumns(['userID'])
                ->onDelete('CASCADE'),
        ]),

    DatabaseTable::create('chat1_message')
        ->columns([
            ObjectIdDatabaseTableColumn::create('messageID'),
            NotNullInt10DatabaseTableColumn::create('time'),
            IntDatabaseTableColumn::create('roomID')
                ->length(10),
            IntDatabaseTableColumn::create('userID')
                ->length(10),
            VarcharDatabaseTableColumn::create('username')
                ->length(100)
                ->notNull(),
            NotNullInt10DatabaseTableColumn::create('objectTypeID'),
            MediumblobDatabaseTableColumn::create('payload')
                ->notNull(),
            DefaultFalseBooleanDatabaseTableColumn::create('hasEmbeddedObjects'),
            DefaultFalseBooleanDatabaseTableColumn::create('isDeleted'),
        ])
        ->indices([
            DatabaseTablePrimaryIndex::create()
                ->columns(['messageID']),
            DatabaseTableIndex::create('roomID')
                ->columns(['roomID']),
            DatabaseTableIndex::create('userID')
                ->columns(['userID']),
            DatabaseTableIndex::create('time')
                ->columns(['time']),
        ])
        ->foreignKeys([
            DatabaseTableForeignKey::create()
                ->columns(['userID'])
                ->referencedTable('wcf1_user')
                ->referencedColumns(['userID'])
                ->onDelete('SET NULL'),
            DatabaseTableForeignKey::create()
                ->columns(['roomID'])
                ->referencedTable('chat1_room')
                ->referencedColumns(['roomID'])
                ->onDelete('CASCADE'),
            DatabaseTableForeignKey::create()
                ->columns(['objectTypeID'])
                ->referencedTable('wcf1_object_type')
                ->referencedColumns(['objectTypeID'])
                ->onDelete('CASCADE'),
        ]),

    DatabaseTable::create('chat1_command')
        ->columns([
            ObjectIdDatabaseTableColumn::create('commandID'),
            NotNullInt10DatabaseTableColumn::create('packageID'),
            NotNullVarchar191DatabaseTableColumn::create('identifier'),
            NotNullVarchar191DatabaseTableColumn::create('className'),
        ])
        ->indices([
            DatabaseTablePrimaryIndex::create()
                ->columns(['commandID']),
            DatabaseTableIndex::create('command')
                ->type(DatabaseTableIndex::UNIQUE_TYPE)
                ->columns(['packageID', 'identifier']),
            DatabaseTableIndex::create('className')
                ->type(DatabaseTableIndex::UNIQUE_TYPE)
                ->columns(['className']),
        ])
        ->foreignKeys([
            DatabaseTableForeignKey::create()
                ->columns(['packageID'])
                ->referencedTable('wcf1_package')
                ->referencedColumns(['packageID'])
                ->onDelete('CASCADE'),
        ]),

    DatabaseTable::create('chat1_command_trigger')
        ->columns([
            ObjectIdDatabaseTableColumn::create('triggerID'),
            NotNullVarchar191DatabaseTableColumn::create('commandTrigger'),
            NotNullInt10DatabaseTableColumn::create('commandID'),
        ])
        ->indices([
            DatabaseTablePrimaryIndex::create()
                ->columns(['triggerID']),
            DatabaseTableIndex::create('commandTrigger')
                ->type(DatabaseTableIndex::UNIQUE_TYPE)
                ->columns(['commandTrigger']),
            DatabaseTableIndex::create('commandID')
                ->columns(['commandID']),
        ])
        ->foreignKeys([
            DatabaseTableForeignKey::create()
                ->columns(['commandID'])
                ->referencedTable('chat1_command')
                ->referencedColumns(['commandID'])
                ->onDelete('CASCADE'),
        ]),

    DatabaseTable::create('chat1_session')
        ->columns([
            NotNullInt10DatabaseTableColumn::create('roomID'),
            NotNullInt10DatabaseTableColumn::create('userID'),
            BinaryDatabaseTableColumn::create('sessionID')
                ->length(16)
                ->notNull(),
            NotNullInt10DatabaseTableColumn::create('lastRequest'),
        ])
        ->indices([
            DatabaseTablePrimaryIndex::create()
                ->columns(['roomID', 'userID', 'sessionID']),
            DatabaseTableIndex::create('userID')
                ->columns(['userID', 'sessionID']),
            DatabaseTableIndex::create('sessionID')
                ->columns(['sessionID']),
            DatabaseTableIndex::create('lastRequest')
                ->columns(['lastRequest']),
        ])
        ->foreignKeys([
            DatabaseTableForeignKey::create()
                ->columns(['roomID'])
                ->referencedTable('chat1_room')
                ->referencedColumns(['roomID'])
                ->onDelete('CASCADE'),
            DatabaseTableForeignKey::create()
                ->columns(['userID'])
                ->referencedTable('wcf1_user')
                ->referencedColumns(['userID'])
                ->onDelete('CASCADE'),
        ]),

    DatabaseTable::create('chat1_suspension')
        ->columns([
            ObjectIdDatabaseTableColumn::create('suspensionID'),
            NotNullInt10DatabaseTableColumn::create('time'),
            IntDatabaseTableColumn::create('expires')
                ->length(10),
            IntDatabaseTableColumn::create('roomID')
                ->length(10),
            NotNullInt10DatabaseTableColumn::create('userID'),
            NotNullInt10DatabaseTableColumn::create('objectTypeID'),
            NotNullVarchar255DatabaseTableColumn::create('reason'),
            IntDatabaseTableColumn::create('judgeID')
                ->length(10),
            VarcharDatabaseTableColumn::create('judge')
                ->length(100)
                ->notNull(),
            IntDatabaseTableColumn::create('revoked')
                ->length(10),
            IntDatabaseTableColumn::create('revokerID')
                ->length(10),
            VarcharDatabaseTableColumn::create('revoker')
                ->length(100),
        ])
        ->indices([
            DatabaseTablePrimaryIndex::create()
                ->columns(['suspensionID']),
            DatabaseTableIndex::create('roomID')
                ->columns(['roomID', 'userID', 'objectTypeID']),
            DatabaseTableIndex::create('userID')
                ->columns(['userID']),
            DatabaseTableIndex::create('objectTypeID')
                ->columns(['objectTypeID', 'roomID']),
            DatabaseTableIndex::create('time')
                ->columns(['time']),
            DatabaseTableIndex::create('judgeID')
                ->columns(['judgeID']),
        ])
        ->foreignKeys([
            DatabaseTableForeignKey::create()
                ->columns(['roomID'])
                ->referencedTable('chat1_room')
                ->referencedColumns(['roomID'])
                ->onDelete('CASCADE'),
            DatabaseTableForeignKey::create()
                ->columns(['userID'])
                ->referencedTable('wcf1_user')
                ->referencedColumns(['userID'])
                ->onDelete('CASCADE'),
            DatabaseTableForeignKey::create()
                ->columns(['judgeID'])
                ->referencedTable('wcf1_user')
                ->referencedColumns(['userID'])
                ->onDelete('SET NULL'),
            DatabaseTableForeignKey::create()
                ->columns(['revokerID'])
                ->referencedTable('wcf1_user')
                ->referencedColumns(['userID'])
                ->onDelete('SET NULL'),
            DatabaseTableForeignKey::create()
                ->columns(['objectTypeID'])
                ->referencedTable('wcf1_object_type')
                ->referencedColumns(['objectTypeID'])
                ->onDelete('CASCADE'),
        ]),

    DatabaseTable::create('chat1_room_temporary_invite')
        ->columns([
            NotNullInt10DatabaseTableColumn::create('roomID'),
            NotNullInt10DatabaseTableColumn::create('userID'),
        ])
        ->indices([
            DatabaseTablePrimaryIndex::create()
                ->columns(['roomID', 'userID']),
            DatabaseTableIndex::create('userID')
                ->columns(['userID']),
        ])
        ->foreignKeys([
            DatabaseTableForeignKey::create()
                ->columns(['roomID'])
                ->referencedTable('chat1_room')
                ->referencedColumns(['roomID'])
                ->onDelete('CASCADE'),
            DatabaseTableForeignKey::create()
                ->columns(['userID'])
                ->referencedTable('wcf1_user')
                ->referencedColumns(['userID'])
                ->onDelete('CASCADE'),
        ]),
];
