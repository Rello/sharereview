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
     * Generate report and store in the given folder for the given user
     */
    public function generate(string $folder, string $type = 'pdf', ?string $uid = null): string {
        if ($uid === null) {
            $uid = $this->userSession->getUser()->getUID();
        }
        $data = $this->shareService->read(false);

        if ($type === 'csv') {
            $content = $this->buildCsv($data);
            $extension = 'csv';
        } else {
            $content = $this->buildPdf($data);
            $extension = 'pdf';
        }

        $userFolder = $this->rootFolder->getUserFolder($uid);
        $target = $userFolder->get($folder);
        $fileName = 'share-report-' . date('YmdHis') . '.' . $extension;
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
        $type = $this->config->getAppValue('sharereview', 'reportType', 'pdf');
        if ($owner === '' || $folder === '') {
            return;
        }
        $this->generate($folder, $type, $owner);
    }

    private function buildCsv(array $rows): string {
        $lines = [];
        $lines[] = implode(',', ['App', 'Object', 'Initiator', 'Type', 'Permissions', 'Time']);
        foreach ($rows as $row) {
            [$shareType, $recipient] = array_pad(explode(';', (string)$row['type'], 2), 2, '');
            $typeText = $this->formatType((int)$shareType, $recipient);
            $permText = $this->formatPermission((int)$row['permissions']);
            $timeText = $this->formatTime((string)$row['time']);
            $lines[] = implode(',', [
                $this->escapeCsv((string)$row['app']),
                $this->escapeCsv((string)$row['object']),
                $this->escapeCsv((string)$row['initiator']),
                $this->escapeCsv($typeText),
                $this->escapeCsv($permText),
                $this->escapeCsv($timeText),
            ]);
        }
        return implode("\n", $lines);
    }

    private function escapeCsv(string $text): string {
        $escaped = str_replace('"', '""', $text);
        if (str_contains($text, ',') || str_contains($text, '"') || str_contains($text, "\n")) {
            return '"' . $escaped . '"';
        }
        return $escaped;
    }

    private function buildPdf(array $rows): string {
        $header = [];
        $header[] = 'Share Review Report';
        $header[] = 'Audit date: ' . (new \DateTime())->format('d.m.Y, H:i:s');
        $header[] = '';
        $header[] = $this->formatRow([
            'App',
            'Object'
        ], [15, 105]);
        $header[] = $this->formatRow([
            '',
            'Initiator',
            'Type',
            'Permissions',
            'Time'
        ], [15, 20, 53, 12, 20]);
        $header[] = '';

        $body = [];
        foreach ($rows as $row) {
            [$shareType, $recipient] = array_pad(explode(';', (string)$row['type'], 2), 2, '');
            $typeText = $this->formatType((int)$shareType, $recipient);
            $permText = $this->formatPermission((int)$row['permissions']);
            $timeText = $this->formatTime((string)$row['time']);
            $body[] = $this->formatRow([
                (string)$row['app'],
                (string)$row['object']
            ], [15, 105]);
            $body[] = $this->formatRow([
                '',
                (string)$row['initiator'],
                $typeText,
                $permText,
                $timeText,
            ], [15, 20, 53, 12, 20]);
            $body[] = '';
        }

        $fontSize = 9;
        $lineHeight = 11;
        $width = 842;
        $height = 595;
        $leftMargin = 36;
        $topMargin = 40;
        $bottomMargin = 40;

        $linesPerPage = intdiv($height - $topMargin - $bottomMargin, $lineHeight);
        $usableLines = $linesPerPage - count($header);
        $pages = [];
        for ($i = 0; $i < count($body); $i += $usableLines) {
            $pages[] = array_merge($header, array_slice($body, $i, $usableLines));
        }
        if ($pages === []) {
            $pages[] = $header;
        }

        $objects = [];
        $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        $pageNums = [];

        $fontObjNum = 3;
        $objects[$fontObjNum] = '<< /Type /Font /Subtype /Type1 /BaseFont /Courier >>';
        $nextObj = $fontObjNum + 1;

        foreach ($pages as $pageLines) {
            $contentStream = "BT\n/F1 {$fontSize} Tf\n{$leftMargin} " . ($height - $topMargin) . " Td\n";
            foreach ($pageLines as $line) {
                $contentStream .= '(' . $this->escapePdfText($line) . ") Tj\n0 -{$lineHeight} Td\n";
            }
            $contentStream .= "ET";
            $length = strlen($contentStream);

            $contentNum = $nextObj++;
            $objects[$contentNum] = "<< /Length {$length} >>\nstream\n{$contentStream}\nendstream";

            $pageNum = $nextObj++;
            $objects[$pageNum] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 {$width} {$height}] /Contents {$contentNum} 0 R /Resources << /Font << /F1 {$fontObjNum} 0 R >> >> >>";
            $pageNums[] = $pageNum;
        }

        $objects[2] = '<< /Type /Pages /Kids [' . implode(' ', array_map(fn($n) => "$n 0 R", $pageNums)) . '] /Count ' . count($pageNums) . ' >>';

        ksort($objects);
        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        for ($i = 1; $i <= count($objects); $i++) {
            $offsets[$i] = strlen($pdf);
            $pdf .= $i . " 0 obj\n" . $objects[$i] . "\nendobj\n";
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

    private function formatRow(array $fields, array $widths): string {
        $parts = [];
        foreach ($fields as $i => $value) {
            $parts[] = str_pad(substr($value, 0, $widths[$i]), $widths[$i]);
        }
        return implode(' ', $parts);
    }

    private function formatTime(string $time): string {
        try {
            $dt = new \DateTime($time);
        } catch (\Exception $e) {
            $dt = new \DateTime();
        }
        return $dt->format('d.m.Y, H:i:s');
    }
}
