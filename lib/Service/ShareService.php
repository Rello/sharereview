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
use OCP\IConfig;
use OCP\IAppConfig;
use OCP\IUserSession;

class ShareService {

	/** @var IAppConfig */
	protected $appConfig;
	/** @var IConfig */
	protected $config;
	/** @var LoggerInterface */
	private $logger;
	/** @var ShareManager */
	private $shareManager;
	/** @var IRootFolder */
	private $rootFolder;
	/** @var IUserSession */
	private $userSession;

	public function __construct(
		IAppConfig      $appConfig,
		IConfig         $config,
		LoggerInterface $logger,
		ShareManager    $shareManager,
		IUserSession    $userSession,
		IRootFolder     $rootFolder
	) {
		$this->appConfig = $appConfig;
		$this->config = $config;
		$this->logger = $logger;
		$this->shareManager = $shareManager;
		$this->rootFolder = $rootFolder;
		$this->userSession = $userSession;
	}

	/**
	 * get all shares for a report
	 *
	 * @return array
	 */
	public function read($onlyNew) {
		$user = $this->userSession->getUser();
		$userTimestamp = $this->config->getUserValue($user->getUID(), 'sharereview', 'reviewTimestamp', 0);

		$shares = $this->shareManager->getAllShares();
		$formated = [];


		foreach ($shares as $share) {
			if ($onlyNew && $share->getShareTime()->format('U') <= $userTimestamp) continue;
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
			$data['action'] = 'ocinternal:' . $share->getId();
		}
		if ($share->getShareType() === IShare::TYPE_GROUP) {
			$data['type'] = 'group';
			$data['recipient'] = $share->getSharedWith();
			$data['action'] = 'ocinternal:' . $share->getId();
		}
		if ($share->getShareType() === IShare::TYPE_LINK) {
			$data['type'] = 'link';
			$data['recipient'] = '';
			$data['action'] = 'ocinternal:' . $share->getId();
		}
		if ($share->getShareType() === IShare::TYPE_EMAIL) {
			$data['type'] = 'email';
			$data['recipient'] = $share->getSharedWith();
			$data['action'] = 'ocMailShare:' . $share->getId();
		}
		if ($share->getShareType() === IShare::TYPE_REMOTE) {
			$data['type'] = 'federated';
			$data['recipient'] = $share->getSharedWith();
			$data['action'] = 'ocFederatedSharing:' . $share->getId();
		}

		return $data;
	}

	public function confirm($timestamp) {
		$user = $this->userSession->getUser();
		$this->config->setUserValue($user->getUID(), 'sharereview', 'reviewTimestamp', $timestamp);
		return $timestamp;
	}

	/**
	 * app can only be used when it is restricted to at least one group for security reasons
	 *
	 * @return bool
	 */
	public function isSecured() {
		$enabled = $this->appConfig->getAllValues('sharereview', 'enabled')['enabled'];
		if ($enabled !== 'yes') {
			return true;
		} else {
			return false;
		}
	}
}