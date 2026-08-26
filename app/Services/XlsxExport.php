<?php

declare(strict_types=1);

namespace App\Services;

use ZipArchive;

final class XlsxExport
{
    /**
     * Stream a .xlsx file and stop the request.
     *
     * @param list<string> $headers
     * @param list<list<string>> $rows
     */
    public static function download(string $filename, string $sheetName, array $headers, array $rows): never
    {
        $filename = self::safeFilename($filename);
        $binary = self::build($sheetName, $headers, $rows);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . (string) strlen($binary));
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Pragma: no-cache');
        echo $binary;
        exit;
    }

    /**
     * @param list<string> $headers
     * @param list<list<string>> $rows
     */
    public static function build(string $sheetName, array $headers, array $rows): string
    {
        $sheetName = self::safeSheetName($sheetName);
        $dir = STORAGE_PATH . '/tmp';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $path = tempnam($dir, 'xlsx');
        if ($path === false) {
            throw new \RuntimeException('Could not create a temporary export file.');
        }

        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::OVERWRITE) !== true) {
            @unlink($path);
            throw new \RuntimeException('Could not create the Excel file.');
        }

        $zip->addFromString('[Content_Types].xml', self::contentTypes());
        $zip->addFromString('_rels/.rels', self::rels());
        $zip->addFromString('xl/workbook.xml', self::workbook($sheetName));
        $zip->addFromString('xl/_rels/workbook.xml.rels', self::workbookRels());
        $zip->addFromString('xl/styles.xml', self::styles());
        $zip->addFromString('xl/worksheets/sheet1.xml', self::sheet($headers, $rows));
        $zip->close();

        $binary = (string) file_get_contents($path);
        @unlink($path);
        return $binary;
    }

    /**
     * @param list<string> $headers
     * @param list<list<string>> $rows
     */
    private static function sheet(array $headers, array $rows): string
    {
        $widths = [20, 28, 22, 30, 16, 16, 42, 14];
        $colsXml = '<cols>';
        foreach ($headers as $i => $_header) {
            $width = $widths[$i] ?? 18;
            $n = $i + 1;
            $colsXml .= '<col min="' . $n . '" max="' . $n . '" width="' . $width . '" customWidth="1"/>';
        }
        $colsXml .= '</cols>';

        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xml:space="preserve">'
            . '<sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
            . $colsXml
            . '<sheetData>';

        $xml .= self::row(1, $headers, 1);
        $r = 2;
        foreach ($rows as $row) {
            $xml .= self::row($r, $row, 2);
            $r++;
        }

        $xml .= '</sheetData></worksheet>';
        return $xml;
    }

    /**
     * @param list<string> $values
     */
    private static function row(int $rowNum, array $values, int $style): string
    {
        $xml = '<row r="' . $rowNum . '">';
        foreach (array_values($values) as $i => $value) {
            $ref = self::col($i) . $rowNum;
            $xml .= '<c r="' . $ref . '" t="inlineStr" s="' . $style . '"><is><t>' . self::xml(self::cellText((string) $value)) . '</t></is></c>';
        }
        return $xml . '</row>';
    }

    private static function col(int $index): string
    {
        $name = '';
        $n = $index + 1;
        while ($n > 0) {
            $n--;
            $name = chr(65 + ($n % 26)) . $name;
            $n = intdiv($n, 26);
        }
        return $name;
    }

    private static function cellText(string $value): string
    {
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $value) ?? $value;
        return $value;
    }

    private static function xml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private static function safeFilename(string $filename): string
    {
        $filename = preg_replace('/[^A-Za-z0-9._-]+/', '-', $filename) ?? 'export.xlsx';
        if (!str_ends_with(strtolower($filename), '.xlsx')) {
            $filename .= '.xlsx';
        }
        return $filename !== '.xlsx' ? $filename : 'export.xlsx';
    }

    private static function safeSheetName(string $name): string
    {
        $name = str_replace(['\\', '/', '*', '?', ':', '[', ']'], ' ', $name);
        $name = trim($name);
        if ($name === '') {
            $name = 'Sheet1';
        }
        return mb_substr($name, 0, 31);
    }

    private static function contentTypes(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '</Types>';
    }

    private static function rels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';
    }

    private static function workbook(string $sheetName): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="' . self::xml($sheetName) . '" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>';
    }

    private static function workbookRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>';
    }

    private static function styles(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="2">'
            . '<font><sz val="11"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="11"/><name val="Calibri"/><color rgb="FF13241C"/></font>'
            . '</fonts>'
            . '<fills count="3">'
            . '<fill><patternFill patternType="none"/></fill>'
            . '<fill><patternFill patternType="gray125"/></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FFC9A227"/><bgColor indexed="64"/></patternFill></fill>'
            . '</fills>'
            . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            . '<cellStyleXfs count="1"><xf/></cellStyleXfs>'
            . '<cellXfs count="3">'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            . '<xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1" applyAlignment="1"><alignment wrapText="1" vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0" applyAlignment="1"><alignment wrapText="1" vertical="top"/></xf>'
            . '</cellXfs>'
            . '</styleSheet>';
    }
}
