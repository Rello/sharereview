<?php
/**
 * Share Review
 *
 * SPDX-FileCopyrightText: 2024 Marcel Scherello
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\ShareReview\Db;

use OCP\IDBConnection;

class ShareMapper {
    private IDBConnection $db;

    public function __construct(IDBConnection $db) {
        $this->db = $db;
    }

    /**
     * Read all shares from the database.
     *
     * @return array<int, array<string, mixed>>
     */
    public function findAll(): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('id', 'share_type', 'uid_owner', 'uid_initiator', 'share_with', 'permissions', 'stime', 'token', 'file_target')
            ->from('share');

        $result = $qb->executeQuery();
        return $result->fetchAll();
    }
}
