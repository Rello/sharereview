<?php
/**
 * Share Review
 *
 * SPDX-FileCopyrightText: 2024 Marcel Scherello
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\ShareReview\Helper;

use OCP\IUser;
use OCP\IUserManager;
use Psr\Log\LoggerInterface;

class DeckHelper {
	private IUserManager $userManager;
	private LoggerInterface $logger;

	public function __construct(IUserManager $userManager, LoggerInterface $logger) {
		$this->userManager = $userManager;
		$this->logger = $logger;
	}

	public function getDeckDisplayName(string $deckId): string {
		return 'Deck card ' . $deckId;
	}
}
