<?php
/**
 * Share Review
 *
 * SPDX-FileCopyrightText: 2024 Marcel Scherello
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\ShareReview\Controller;

use OCA\ShareReview\Service\ShareService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;
use Psr\Log\LoggerInterface;

class OutputController extends Controller {
	private $logger;
	private $ShareService;

	public function __construct(
		string          $appName,
		IRequest        $request,
		LoggerInterface $logger,
		ShareService    $ShareService
	) {
		parent::__construct($appName, $request);
		$this->logger = $logger;
		$this->ShareService = $ShareService;
	}

	/**
	 * get the data when requested from internal page
	 *
	 * @NoAdminRequired
	 * @return DataResponse
	 */
	public function read() {
		$data = $this->ShareService->read();
		return new DataResponse($data, HTTP::STATUS_OK);
	}

	/**
	 * get the data when requested from internal page
	 *
	 * @NoAdminRequired
	 * @param $shareId
	 * @return DataResponse
	 */
	public function delete($shareId) {
		$result = $this->ShareService->delete($shareId);
		return new DataResponse($result, HTTP::STATUS_OK);
	}

}