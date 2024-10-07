<?php
/**
 * Share Review
 *
 * SPDX-FileCopyrightText: 2024 Marcel Scherello
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\ShareReview\Service;

use OCA\ShareReview\Helper\UserHelper;
use OCA\ShareReview\Helper\GroupHelper;
use OCA\ShareReview\Sources\SourceEvent;
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
use OCP\EventDispatcher\IEventDispatcher;

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
	protected UserHelper $userHelper;
	protected GroupHelper $groupHelper;
	/** @var IEventDispatcher */
	private $dispatcher;

	public function __construct(
		IAppConfig       $appConfig,
		IConfig          $config,
		LoggerInterface  $logger,
		ShareManager     $shareManager,
		IUserSession     $userSession,
		UserHelper       $userHelper,
		GroupHelper      $groupHelper,
		IRootFolder      $rootFolder,
		IEventDispatcher $dispatcher
	) {
		$this->appConfig = $appConfig;
		$this->config = $config;
		$this->logger = $logger;
		$this->shareManager = $shareManager;
		$this->rootFolder = $rootFolder;
		$this->userHelper = $userHelper;
		$this->groupHelper = $groupHelper;
		$this->userSession = $userSession;
		$this->dispatcher = $dispatcher;
	}

	/**
	 * get all shares for a report
	 *
	 * @param $onlyNew
	 * @return array
	 * @throws NotFoundException
	 * @throws NotPermittedException
	 */
	public function read($onlyNew) {
		$user = $this->userSession->getUser();
		$userTimestamp = $this->config->getUserValue($user->getUID(), 'sharereview', 'reviewTimestamp', 0);
		$formated = [];

		$shares = $this->getFileShares();
		$appShares = $this->getAppShares();

		$shares = array_merge($shares, $appShares);

		foreach ($shares as $share) {
			if ($onlyNew && $share->getShareTime()->format('U') <= $userTimestamp) continue;
			$formatedShare = $this->formatShare($share);
			if ($formatedShare !== []) $formated[] = $formatedShare;
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
	 * @param $share
	 * @return array
	 * @throws NotFoundException
	 * @throws NotPermittedException
	 */
	private function formatShare($share): array {

		if ($share['type'] === IShare::TYPE_GROUP) {
			$share['recipient'] = $share['recipient'] != '' ? $this->groupHelper->getGroupDisplayName($share['recipient']) : '';
		} elseif ($share['type'] != IShare::TYPE_EMAIL && $share['type'] != IShare::TYPE_LINK) {
			$share['recipient'] = $share['recipient'] != '' ? $this->userHelper->getUserDisplayName($share['recipient']) : '';
		}
		$share['type'] = $share['type'] . ';' . $share['recipient'];
		unset($share['recipient']);
		$share['initiator'] = $share['initiator'] != '' ? $this->userHelper->getUserDisplayName($share['initiator']) : '';

		return $share;
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
		$enabled = $this->appConfig->getFilteredValues('sharereview')['enabled'];
		if ($enabled !== 'yes') {
			return true;
		} else {
			return false;
		}
	}

	private function getFileShares() {
		$shares = $this->shareManager->getAllShares();

		foreach ($shares as $share) {
			if ($this->userHelper->isValidOwner($share->getShareOwner())) {
				$userFolder = $this->rootFolder->getUserFolder($share->getShareOwner());
				$nodes = $userFolder->getById($share->getNodeId());
				$node = array_shift($nodes);

				if ($node !== null && $userFolder !== null) {
					$path = $userFolder->getRelativePath($node->getPath());
				} else {
					$path = 'invalid share (*) ' . $share->getTarget();
				}
			} else {
				$path = 'invalid share (*) ' . $share->getTarget();
			}

			$recipient = $share->getSharedWith();

			if ($share->getShareType() === IShare::TYPE_USER) {
				$action = 'ocinternal:' . $share->getId();
			}
			if ($share->getShareType() === IShare::TYPE_GROUP) {
				$action = 'ocinternal:' . $share->getId();
			}
			if ($share->getShareType() === IShare::TYPE_LINK) {
				$action = 'ocinternal:' . $share->getId();
				$recipient = $share->getToken();
			}
			if ($share->getShareType() === IShare::TYPE_EMAIL) {
				$action = 'ocMailShare:' . $share->getId();
			}
			if ($share->getShareType() === IShare::TYPE_REMOTE) {
				$action = 'ocFederatedSharing:' . $share->getId();
			}

			$data = [
				'app' => 'Files',
				'object' => $path,
				'initiator' => $share->getSharedBy(),
				'type' => $share->getShareType(),
				'recipient' => $recipient,
				'permissions' => $share->getPermissions(),
				'time' => $share->getShareTime()->format(\DATE_ATOM),
				'action' => $action,
			];

			$formated[] = $data;
		}
		return $formated;
	}

	private function getAppShares() {
		foreach ($this->getRegisteredSources() as $key => $app) {
			$apps[$key] = $app->getShares();
		}

		foreach ($apps as $shares) {
			foreach ($shares as $share) {
				$formated[] = $share;
			}
		}
		return $formated;
	}

	private function getRegisteredSources() {
		$dataSources = [];
		$event = new SourceEvent();
		$this->dispatcher->dispatchTyped($event);

		foreach ($event->getSources() as $class) {
			try {
				$uniqueId = '99' . \OC::$server->get($class)->getId();

				if (isset($dataSources[$uniqueId])) {
					$this->logger->error(new \InvalidArgumentException('Data source with the same ID already registered: ' . \OC::$server->get($class)
																																		 ->getName()));
					continue;
				}
				$dataSources[$uniqueId] = \OC::$server->get($class);
			} catch (\Error $e) {
				$this->logger->error('Can not initialize data source: ' . json_encode($class));
				$this->logger->error($e->getMessage());
			}
		}
		return $dataSources;
	}
}