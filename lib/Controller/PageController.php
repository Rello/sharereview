<?php
/**
 * Share Review
 *
 * SPDX-FileCopyrightText: 2024 Marcel Scherello
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\ShareReview\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;
use OCP\AppFramework\Services\IInitialState;
use OCP\IConfig;
use OCP\IUserSession;
use Psr\Log\LoggerInterface;
use OCA\Text\Event\LoadEditor;

/**
 * Controller class for main page.
 */
class PageController extends Controller {
	private $logger;
	/** @var IInitialState */
	protected $initialState;
	/** @var IConfig */
	protected $config;
	/** @var IUserSession */
	private $userSession;

	public function __construct(
		string          $appName,
		IRequest        $request,
		LoggerInterface $logger,
		IInitialState   $initialState,
		IConfig         $config,
		IUserSession    $userSession,
	) {
		parent::__construct($appName, $request);
		$this->logger = $logger;
		$this->initialState = $initialState;
		$this->config = $config;
		$this->userSession = $userSession;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
        public function index() {
                $user = $this->userSession->getUser();
                $this->initialState->provideInitialState('reviewTimestamp', $this->config->getUserValue($user->getUID(), 'sharereview', 'reviewTimestamp', 0));
                $this->initialState->provideInitialState('showTalk', $this->config->getUserValue($user->getUID(), 'sharereview', 'showTalk', 'false'));
                $this->initialState->provideInitialState('reportFolder', $this->config->getAppValue('sharereview', 'reportFolder', ''));
                $this->initialState->provideInitialState('schedule', $this->config->getAppValue('sharereview', 'schedule', 'none'));
                $this->initialState->provideInitialState('reportType', $this->config->getAppValue('sharereview', 'reportType', 'pdf'));
                $params = array();
                return new TemplateResponse($this->appName, 'main', $params);
        }
}