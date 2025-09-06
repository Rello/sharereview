<?php
/**
 * Share Review
 *
 * SPDX-FileCopyrightText: 2024 Marcel Scherello
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\ShareReview\Service;

use OCP\Files\IRootFolder;
use OCP\IUserSession;
use OCP\IConfig;

class ReportService {
    private ShareService $shareService;
    private IRootFolder $rootFolder;
    private IUserSession $userSession;
    private IConfig $config;

    public function __construct(ShareService $shareService,
                                IRootFolder $rootFolder,
                                IUserSession $userSession,
                                IConfig $config) {
        $this->shareService = $shareService;
        $this->rootFolder = $rootFolder;
        $this->userSession = $userSession;
        $this->config = $config;
    }

    /**
     * Generate PDF report and store in the given folder for the given user
     */
    public function generate(string $folder, ?string $uid = null): string {
        if ($uid === null) {
            $uid = $this->userSession->getUser()->getUID();
        }
        $data = $this->shareService->read(false);

        $pdf = new \TCPDF();
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 16);
        $pdf->Cell(0, 10, 'Share Review Report', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 10, 'Audit date: ' . (new \DateTime())->format('Y-m-d H:i'), 0, 1);
        $html = '<table border="1" cellpadding="4"><thead><tr>' .
            '<th>App</th><th>Object</th><th>Initiator</th><th>Recipient</th><th>Time</th></tr></thead><tbody>';
        foreach ($data as $row) {
            $html .= '<tr>' .
                '<td>' . htmlspecialchars((string)$row['app']) . '</td>' .
                '<td>' . htmlspecialchars((string)$row['object']) . '</td>' .
                '<td>' . htmlspecialchars((string)$row['initiator']) . '</td>' .
                '<td>' . htmlspecialchars((string)$row['recipient']) . '</td>' .
                '<td>' . htmlspecialchars((string)$row['time']) . '</td>' .
                '</tr>';
        }
        $html .= '</tbody></table>';
        $pdf->writeHTML($html);
        $content = $pdf->Output('', 'S');

        $userFolder = $this->rootFolder->getUserFolder($uid);
        $target = $userFolder->get($folder);
        $fileName = 'share-report-' . date('YmdHis') . '.pdf';
        $file = $target->newFile($fileName);
        $file->putContent($content);

        return $fileName;
    }

    /**
     * Generate report using stored default folder
     */
    public function generateDefault(): void {
        $owner = $this->config->getAppValue('sharereview', 'reportOwner', '');
        $folder = $this->config->getAppValue('sharereview', 'reportFolder', '');
        if ($owner === '' || $folder === '') {
            return;
        }
        $this->generate($folder, $owner);
    }
}
