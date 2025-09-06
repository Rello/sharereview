<?php
/**
 * Share Review
 *
 * SPDX-FileCopyrightText: 2024 Marcel Scherello
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\ShareReview\BackgroundJob;

use OCP\BackgroundJob\TimedJob;
use OCP\IConfig;
use OCA\ShareReview\Service\ReportService;

class GenerateReportJob extends TimedJob {
    private IConfig $config;
    private ReportService $reportService;

    public function __construct(IConfig $config, ReportService $reportService) {
        $this->config = $config;
        $this->reportService = $reportService;
        $this->setInterval($this->getInterval());
    }

    protected function run($argument): void {
        $interval = $this->getInterval();
        if ($interval === 0) {
            return;
        }
        $this->setInterval($interval);
        $this->reportService->generateDefault();
    }

    private function getInterval(): int {
        $schedule = $this->config->getAppValue('sharereview', 'schedule', 'none');
        return match ($schedule) {
            'daily' => 60 * 60 * 24,
            'weekly' => 60 * 60 * 24 * 7,
            'monthly' => 60 * 60 * 24 * 30,
            default => 0,
        };
    }
}
