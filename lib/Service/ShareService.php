<?php
/**
 * Share Review
 *
 * SPDX-FileCopyrightText: 2024 Marcel Scherello
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\ShareReview\Service;

use OCP\Files\NotFoundException;
use OCP\Files\NotPermittedException;
use OCP\Share\Exceptions\ShareNotFound;
use Psr\Log\LoggerInterface;
use OCP\Share\IManager as ShareManager;
use OCP\Share\IShare;
use OCP\Files\IRootFolder;

class ShareService {

	/** @var LoggerInterface */
	private $logger;
	/** @var ShareManager */
	private $shareManager;
	/** @var IRootFolder */
	private $rootFolder;

	public function __construct(
		LoggerInterface $logger,
		ShareManager    $shareManager,
		IRootFolder     $rootFolder
	) {
		$this->logger = $logger;
		$this->shareManager = $shareManager;
		$this->rootFolder = $rootFolder;
	}

	private function getShareTypes(): array {
		return [
			IShare::TYPE_USER,
			IShare::TYPE_GROUP,
			IShare::TYPE_LINK,
			IShare::TYPE_EMAIL,
			IShare::TYPE_REMOTE,
		];
	}

	/**
	 * get all shares for a report
	 *
	 * @return array
	 */
	public function read() {
		$shares = $this->shareManager->getAllShares();
		$formated = [];
		foreach ($shares as $share) {
			$formated[] = $this->formatShare($share);
		}
		return $formated;
	}

	/**
	 * get all shares for a report
	 *
	 * @param $shareId
	 * @return array
	 * @throws ShareNotFound
	 */
	public function delete($shareId) {
		$share = $this->shareManager->getShareById($shareId);
		return $this->shareManager->deleteShare($share);
	}

	/**
	 * @throws NotPermittedException
	 * @throws NotFoundException
	 */
	private function formatShare(IShare $share): array {
		$userFolder = $this->rootFolder->getUserFolder($share->getShareOwner());
		$nodes = $userFolder->getById($share->getNodeId());
		$node = array_shift($nodes);

		$data = [
			//'id' => $share->getId(),
			'path' => $userFolder->getRelativePath($node->getPath()),
			//'name' => $node->getName(),
			//'is_directory' => $node->getType() === 'dir',
			//'file_id' => $share->getNodeId(),
			'owner' => $share->getShareOwner(),
			'initiator' => $share->getSharedBy(),
			'type' => '',
			'permissions' => $share->getPermissions(),
			'recipient' => '',
			'time' => $share->getShareTime()->format(\DATE_ATOM),
			'action' => ''
		];

		if ($share->getShareType() === IShare::TYPE_USER) {
			$data['type'] = 'user';
			$data['recipient'] = $share->getSharedWith();
			$data['action'] = 'ocinternal:'.$share->getId();
		}
		if ($share->getShareType() === IShare::TYPE_GROUP) {
			$data['type'] = 'group';
			$data['recipient'] = $share->getSharedWith();
			$data['action'] = 'ocinternal:'.$share->getId();
		}
		if ($share->getShareType() === IShare::TYPE_LINK) {
			$data['type'] = 'link';
			$data['recipient'] = '';
			$data['action'] = 'ocinternal:'.$share->getId();
		}
		if ($share->getShareType() === IShare::TYPE_EMAIL) {
			$data['type'] = 'email';
			$data['recipient'] = $share->getSharedWith();
			$data['action'] = 'ocMailShare:'.$share->getId();
		}
		if ($share->getShareType() === IShare::TYPE_REMOTE) {
			$data['type'] = 'federated';
			$data['recipient'] = $share->getSharedWith();
			$data['action'] = 'ocFederatedSharing:'.$share->getId();
		}

		return $data;
	}
}