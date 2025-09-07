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

        $content = $this->buildPdf($data);

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

    private function buildPdf(array $rows): string {
        $lines = [];
        $lines[] = 'Share Review Report';
        $lines[] = 'Audit date: ' . (new \DateTime())->format('Y-m-d H:i');
        $lines[] = '';
        $lines[] = $this->formatRow(['App', 'Object', 'Initiator', 'Type', 'Permissions', 'Time']);
        foreach ($rows as $row) {
            [$shareType, $recipient] = array_pad(explode(';', (string)$row['type'], 2), 2, '');
            $typeText = $this->formatType((int)$shareType, $recipient);
            $permText = $this->formatPermission((int)$row['permissions']);
            $lines[] = $this->formatRow([
                (string)$row['app'],
                (string)$row['object'],
                (string)$row['initiator'],
                $typeText,
                $permText,
                (string)$row['time'],
            ]);
        }

        $fontSize = 9;
        $lineHeight = 11;
        $contentStream = "BT\n/F1 {$fontSize} Tf\n72 800 Td\n";
        foreach ($lines as $line) {
            $contentStream .= '(' . $this->escapePdfText($line) . ") Tj\n0 -{$lineHeight} Td\n";
        }
        $contentStream .= "ET";
        $length = strlen($contentStream);

        $objects = [];
        $objects[] = "<< /Type /Catalog /Pages 2 0 R >>";
        $objects[] = "<< /Type /Pages /Kids [3 0 R] /Count 1 >>";
        $objects[] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>";
        $objects[] = "<< /Length {$length} >>\nstream\n{$contentStream}\nendstream";
        $objects[] = "<< /Type /Font /Subtype /Type1 /BaseFont /Courier >>";

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $i => $obj) {
            $offsets[$i + 1] = strlen($pdf);
            $pdf .= ($i + 1) . " 0 obj\n{$obj}\nendobj\n";
        }
        $xrefPos = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }
        $pdf .= "trailer << /Size " . (count($objects) + 1) . " /Root 1 0 R >>\nstartxref\n{$xrefPos}\n%%EOF";

        return $pdf;
    }

    private function formatPermission(int $permission): string {
        switch ($permission) {
            case 1:
            case 17:
                return 'read';
            case 2:
            case 19:
            case 31:
                return 'edit';
            default:
                return 'more';
        }
    }

    private function formatType(int $type, string $recipient): string {
        switch ($type) {
            case 12:
                $label = 'Deck';
                break;
            case 10:
                $label = 'Talk room';
                break;
            case 7:
                $label = 'Team';
                break;
            case 9:
            case 6:
                $label = 'Federation';
                break;
            case 4:
                $label = 'E-mail';
                break;
            case 3:
                $label = 'Link';
                break;
            case 1:
                $label = 'Group';
                break;
            case 0:
                $label = 'User';
                break;
            default:
                $label = 'Other';
                break;
        }
        return $label . ' ' . $recipient;
    }

    private function escapePdfText(string $text): string {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    }

    private function formatRow(array $fields): string {
        $widths = [8, 30, 12, 16, 12, 20];
        $parts = [];
        foreach ($fields as $i => $value) {
            $parts[] = str_pad(substr($value, 0, $widths[$i]), $widths[$i]);
        }
        return implode(' ', $parts);
    }
}
