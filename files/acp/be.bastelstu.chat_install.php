<?php
/*
 * Copyright (c) 2010-2018 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2024-11-20
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

use \wcf\system\box\BoxHandler;

BoxHandler::getInstance()->createBoxCondition( 'be.bastelstu.chat.roomListDashboard'
                                             , 'be.bastelstu.chat.box.roomList.condition'
                                             , 'be.bastelstu.chat.roomFilled'
                                             , [ 'chatRoomIsFilled' => 1 ]
                                             );
