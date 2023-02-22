<?php

/**
 * Deletes orphaned attachments.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core
 * @see https://github.com/WoltLab/com.woltlab.wcf.conversation/blob/f0d4964a0b8042c440d5a3f759078429dc43c5b8/files/acp/update_com.woltlab.wcf.conversation_5.5_cleanup_orphaned_attachments.php
 */

use wcf\data\attachment\AttachmentAction;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\package\SplitNodeException;
use wcf\system\WCF;

$objectType = ObjectTypeCache::getInstance()
    ->getObjectTypeByName('com.woltlab.wcf.attachment.objectType', 'be.bastelstu.chat.message');

$sql = "SELECT  attachmentID
        FROM    wcf1_attachment
        WHERE   objectTypeID = ?
            AND objectID NOT IN (
                SELECT  messageID
                FROM    chat1_message
            )";
$statement = WCF::getDB()->prepare($sql, 100);
$statement->execute([$objectType->objectTypeID]);
$attachmentIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);

if (empty($attachmentIDs)) {
    return;
}

(new AttachmentAction($attachmentIDs, 'delete'))->executeAction();

// If we reached this location we processed at least one attachment.
// If this was the final attachment the next iteration will abort this
// script early, thus not splitting the node.
throw new SplitNodeException();
