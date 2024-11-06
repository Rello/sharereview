<?php
/**
 * Share Review
 *
 * SPDX-FileCopyrightText: 2024 Marcel Scherello
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\ShareReview\Helper;

use OCA\Talk\Manager;
use OCA\Talk\Room;
use Psr\Log\LoggerInterface;

class TalkHelper {
	private Manager $talkManager;
	private LoggerInterface $logger;

	public function __construct(Manager $talkManager, LoggerInterface $logger) {
		$this->talkManager = $talkManager;
		$this->logger = $logger;
	}

	public function getRoomDisplayName(string $token): string {
		$room = $this->talkManager->getRoomByToken($token);
		if ($room instanceof Room) {
			return $room->getName() ? $room->getName() : $token;
		} else {
			return $token . ' (r*)';
		}
	}
}
