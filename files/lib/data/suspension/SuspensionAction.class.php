<?php

/*
 * Copyright (c) 2010-2022 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2026-03-10
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 3
 * or later of the General Public License.
 */

namespace chat\data\suspension;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * Executes suspension-related actions.
 */
class SuspensionAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
    protected $requireACP = [
        'revoke',
    ];

    /**
     * Validates parameters and permissions.
     */
    public function validateRevoke()
    {
        if (empty($this->objects)) {
            $this->readObjects();

            if (empty($this->objects)) {
                throw new UserInputException('objectIDs');
            }
        }

        unset($this->parameters['revoker']);

        WCF::getSession()->checkPermissions([
            'admin.chat.canManageSuspensions',
        ]);

        foreach ($this->getObjects() as $object) {
            if (!$object->isActive()) {
                throw new UserInputException('objectIDs', 'nonActive');
            }
        }
    }

    /**
     * Revokes the suspensions
     */
    public function revoke()
    {
        if (empty($this->objects)) {
            $this->readObjects();
        }

        // User cannot be set during an AJAX request, but may be set by Tim’s Chat itself.
        if (!isset($this->parameters['revoker'])) {
            $this->parameters['revoker'] = WCF::getUser();
        }

        $data = [
            'revoked' => TIME_NOW,
            'revokerID' => $this->parameters['revoker']->userID,
            'revoker' => $this->parameters['revoker']->username,
        ];

        $objectAction = new static(
            $this->getObjects(),
            'update',
            [
                'data' => $data,
            ]
        );
        $objectAction->executeAction();
    }
}
