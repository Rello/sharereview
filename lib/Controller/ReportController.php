<?php
/**
 * Share Review
 *
 * SPDX-FileCopyrightText: 2024 Marcel Scherello
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\ShareReview\Controller;

use OCA\ShareReview\BackgroundJob\GenerateReportJob;
use OCA\ShareReview\Service\ReportService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\BackgroundJob\IJobList;
use OCP\IConfig;
use OCP\IRequest;
use OCP\IUserSession;

class ReportController extends Controller {
    private ReportService $reportService;
    private IConfig $config;
    private IJobList $jobList;
    private IUserSession $userSession;

    public function __construct(string $appName,
                                IRequest $request,
                                ReportService $reportService,
                                IConfig $config,
                                IJobList $jobList,
                                IUserSession $userSession) {
        parent::__construct($appName, $request);
        $this->reportService = $reportService;
        $this->config = $config;
        $this->jobList = $jobList;
        $this->userSession = $userSession;
    }

    /**
     * @NoAdminRequired
     */
    public function export(string $path): DataResponse {
        $name = $this->reportService->generate($path);
        return new DataResponse(['file' => $name], Http::STATUS_OK);
    }

    /**
     * @NoAdminRequired
     */
    public function saveSettings(string $folder, string $schedule): DataResponse {
        $user = $this->userSession->getUser();
        $this->config->setAppValue('sharereview', 'reportOwner', $user->getUID());
        $this->config->setAppValue('sharereview', 'reportFolder', $folder);
        $this->config->setAppValue('sharereview', 'schedule', $schedule);

        $this->jobList->remove(GenerateReportJob::class);
        if ($schedule !== 'none') {
            $this->jobList->add(GenerateReportJob::class);
        }

        return new DataResponse(['status' => 'ok'], Http::STATUS_OK);
    }
}
