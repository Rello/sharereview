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
use Psr\Log\LoggerInterface;
use OCA\Text\Event\LoadEditor;

/**
 * Controller class for main page.
 */
class PageController extends Controller
{
    private $logger;

    public function __construct(
        string $appName,
        IRequest $request,
        LoggerInterface $logger,
    )
    {
        parent::__construct($appName, $request);
        $this->logger = $logger;
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function index()
    {
        $params = array();
        return new TemplateResponse($this->appName, 'main', $params);
    }
}