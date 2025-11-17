<?php

namespace App\Domain\Import\Parsers;

use App\Domain\Import\Contracts\ImportParserInterface;
use App\Domain\Import\Data\ParsedImportData;
use App\Domain\Import\Data\ParsedTransactionData;
use App\Domain\Import\Enums\ImportSource;
use App\Domain\Import\Exceptions\ImportValidationException;

class CsvParser implements ImportParserInterface
{
    private const COMMON_DATE_COLUMNS = ['date', 'transaction date', 'value date', 'posted date', 'booking date', 'dato'];
    private const COMMON_AMOUNT_COLUMNS = ['amount', 'montant', 'montant ttc', 'bedrag', 'betrag'];
    private const COMMON_DEBIT_COLUMNS = ['debit', 'withdrawal', 'out', 'uitgave', 'sortie', 'débit'];
    private const COMMON_CREDIT_COLUMNS = ['credit', 'deposit', 'in', 'inkomst', 'entrée', 'crédit'];
    private const COMMON_PAYEE_COLUMNS = ['payee', 'description', 'beneficiary', 'merchant', 'counterparty', 'naam', 'bénéficiaire', 'intitulé', 'intitulé de l\'opération', 'libellé'];
    private const COMMON_DESCRIPTION_COLUMNS = ['memo', 'details', 'communication', 'notes', 'reference', 'omschrijving', 'commentaire', 'label'];

    private const DATE_FORMATS = [
        'Y-m-d',
        'd/m/Y',
        'm/d/Y',
        'd-m-Y',
        'm-d-Y',
        'Y/m/d',
        'd.m.Y',
        'Y.m.d',
    ];

    public function getSupportedSource(): ImportSource
    {
        return ImportSource::CSV;
    }

    public function validate(string $filePath): bool
    {
        if (!file_exists($filePath)) {
            throw new ImportValidationException(['file' => ['File does not exist']]);
        }

        if (!is_readable($filePath)) {
            throw new ImportValidationException(['file' => ['File is not readable']]);
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $filePath);
        finfo_close($finfo);

        if (!in_array($mimeType, ['text/plain', 'text/csv', 'application/csv'])) {
            throw new ImportValidationException(['file' => ['Invalid file type. Expected CSV file.']]);
        }

        return true;
    }

    public function parse(string $filePath, ?array $mapping = null): ParsedImportData
    {
        \Illuminate\Support\Facades\Log::info('CsvParser: Starting parse', [
            'file_path' => $filePath,
            'mapping' => $mapping,
        ]);

        $this->validate($filePath);

        $encoding = $this->detectEncoding($filePath);
        $delimiter = $this->detectDelimiter($filePath);

        \Illuminate\Support\Facades\Log::info('CsvParser: Encoding and delimiter detected', [
            'encoding' => $encoding,
            'delimiter' => $delimiter,
        ]);

        $file = fopen($filePath, 'r');
        if ($file === false) {
            \Illuminate\Support\Facades\Log::error('CsvParser: Unable to open file', [
                'file_path' => $filePath,
            ]);
            throw new ImportValidationException(['file' => ['Unable to open file']]);
        }

        $headers = fgetcsv($file, 0, $delimiter);
        if ($headers === false) {
            fclose($file);
            \Illuminate\Support\Facades\Log::error('CsvParser: Unable to read CSV headers');
            throw new ImportValidationException(['file' => ['Unable to read CSV headers']]);
        }

        $headers = array_map(fn($h) => $this->normalizeString($h, $encoding), $headers);

        \Illuminate\Support\Facades\Log::info('CsvParser: Headers detected', [
            'headers' => $headers,
        ]);

        // If no mapping provided, suggest detection but don't require it
        $detectedColumns = $mapping ?? $this->detectColumns($headers, $requireAll = false);

        \Illuminate\Support\Facades\Log::info('CsvParser: Column mapping', [
            'detected_columns' => $detectedColumns,
            'mapping_provided' => $mapping !== null,
        ]);

        $transactions = [];
        $sampleRows = [];
        $rowNumber = 1;
        $errors = [];

        // If no mapping provided, just collect sample rows for preview (don't parse transactions yet)
        if ($mapping === null) {
            while (($row = fgetcsv($file, 0, $delimiter)) !== false) {
                $rowNumber++;

                if ($this->isEmptyRow($row)) {
                    continue;
                }

                $row = array_map(fn($cell) => $this->normalizeString($cell, $encoding), $row);

                // Adjust row to match header count
                if (count($headers) !== count($row)) {
                    \Illuminate\Support\Facades\Log::warning('CsvParser: Row column count mismatch - adjusting', [
                        'row_number' => $rowNumber,
                        'expected' => count($headers),
                        'actual' => count($row),
                    ]);

                    // Pad with empty strings or truncate to match header count
                    if (count($row) < count($headers)) {
                        $row = array_pad($row, count($headers), '');
                    } else {
                        $row = array_slice($row, 0, count($headers));
                    }
                }

                $rowData = array_combine($headers, $row);

                if (count($sampleRows) < 5) {
                    $sampleRows[] = $rowData;
                } else {
                    break; // Only need 5 sample rows for preview
                }
            }

            fclose($file);

            \Illuminate\Support\Facades\Log::info('CsvParser: Preview mode - collected sample rows', [
                'sample_count' => count($sampleRows),
                'detected_columns' => $detectedColumns,
            ]);

            return new ParsedImportData(
                transactions: [],
                detected_columns: $detectedColumns,
                sample_rows: $sampleRows,
                total_rows: 0 // Will be counted during actual import
            );
        }

        // Full parse with mapping provided
        while (($row = fgetcsv($file, 0, $delimiter)) !== false) {
            $rowNumber++;

            if ($this->isEmptyRow($row)) {
                continue;
            }

            $row = array_map(fn($cell) => $this->normalizeString($cell, $encoding), $row);

            // Adjust row to match header count
            if (count($headers) !== count($row)) {
                \Illuminate\Support\Facades\Log::warning('CsvParser: Row column count mismatch - adjusting', [
                    'row_number' => $rowNumber,
                    'expected' => count($headers),
                    'actual' => count($row),
                ]);

                // Pad with empty strings or truncate to match header count
                if (count($row) < count($headers)) {
                    $row = array_pad($row, count($headers), '');
                } else {
                    $row = array_slice($row, 0, count($headers));
                }
            }

            $rowData = array_combine($headers, $row);

            if (count($sampleRows) < 5) {
                $sampleRows[] = $rowData;
            }

            try {
                $transaction = $this->parseRow($rowData, $detectedColumns, $rowNumber);
                $transactions[] = $transaction;
            } catch (\Exception $e) {
                $errors[] = [
                    'row' => $rowNumber,
                    'message' => $e->getMessage(),
                ];
            }
        }

        fclose($file);

        \Illuminate\Support\Facades\Log::info('CsvParser: Parse complete', [
            'total_transactions' => count($transactions),
            'total_errors' => count($errors),
        ]);

        if (!empty($errors)) {
            \Illuminate\Support\Facades\Log::warning('CsvParser: Some rows had validation errors and were skipped', [
                'errors' => $errors,
                'error_count' => count($errors),
            ]);
        }

        return new ParsedImportData(
            transactions: $transactions,
            detected_columns: $detectedColumns,
            sample_rows: $sampleRows,
            total_rows: count($transactions)
        );
    }

    private function detectEncoding(string $filePath): string
    {
        $content = file_get_contents($filePath, false, null, 0, 10000);
        $encoding = mb_detect_encoding($content, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);

        return $encoding ?: 'UTF-8';
    }

    private function detectDelimiter(string $filePath): string
    {
        $file = fopen($filePath, 'r');
        $firstLine = fgets($file);
        fclose($file);

        $delimiters = [',', ';', "\t", '|'];
        $counts = [];

        foreach ($delimiters as $delimiter) {
            $counts[$delimiter] = substr_count($firstLine, $delimiter);
        }

        arsort($counts);
        return array_key_first($counts);
    }

    private function detectColumns(array $headers, bool $requireAll = true): array
    {
        $mapping = [];

        foreach ($headers as $index => $header) {
            $normalized = strtolower(trim($header));

            if ($this->matchesColumn($normalized, self::COMMON_DATE_COLUMNS)) {
                $mapping['date'] = $header;
            } elseif ($this->matchesColumn($normalized, self::COMMON_AMOUNT_COLUMNS)) {
                $mapping['amount'] = $header;
            } elseif ($this->matchesColumn($normalized, self::COMMON_DEBIT_COLUMNS)) {
                $mapping['debit'] = $header;
            } elseif ($this->matchesColumn($normalized, self::COMMON_CREDIT_COLUMNS)) {
                $mapping['credit'] = $header;
            } elseif ($this->matchesColumn($normalized, self::COMMON_PAYEE_COLUMNS) && !isset($mapping['payee'])) {
                $mapping['payee'] = $header;
            } elseif ($this->matchesColumn($normalized, self::COMMON_DESCRIPTION_COLUMNS) && !isset($mapping['description'])) {
                $mapping['description'] = $header;
            }
        }

        // Only validate if required (i.e., when actually processing, not during initial parse)
        if ($requireAll) {
            if (!isset($mapping['date'])) {
                throw new ImportValidationException(['mapping' => ['Unable to detect date column']]);
            }

            if (!isset($mapping['amount']) && (!isset($mapping['debit']) || !isset($mapping['credit']))) {
                throw new ImportValidationException(['mapping' => ['Unable to detect amount columns']]);
            }

            if (!isset($mapping['payee'])) {
                throw new ImportValidationException(['mapping' => ['Unable to detect payee column']]);
            }
        }

        return $mapping;
    }

    private function matchesColumn(string $header, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if (str_contains($header, strtolower($pattern))) {
                return true;
            }
        }
        return false;
    }

    private function parseRow(array $rowData, array $mapping, int $rowNumber): ParsedTransactionData
    {
        $date = $this->parseDate($rowData[$mapping['date']] ?? '', $rowNumber);

        $amount = null;
        $debit = null;
        $credit = null;

        if (isset($mapping['amount'])) {
            $amount = $this->parseAmount($rowData[$mapping['amount']] ?? '0');
        } else {
            $debit = isset($mapping['debit']) ? $this->parseAmount($rowData[$mapping['debit']] ?? '0') : null;
            $credit = isset($mapping['credit']) ? $this->parseAmount($rowData[$mapping['credit']] ?? '0') : null;
        }

        $payee = trim($rowData[$mapping['payee']] ?? '');
        if (empty($payee)) {
            throw new \Exception('Payee is required');
        }

        $description = isset($mapping['description']) ? trim($rowData[$mapping['description']] ?? '') : null;

        return new ParsedTransactionData(
            date: $date,
            amount: $amount,
            debit: $debit,
            credit: $credit,
            payee: $payee,
            description: $description,
            row_number: $rowNumber,
            raw_data: $rowData
        );
    }

    private function parseDate(string $dateString, int $rowNumber): string
    {
        $dateString = trim($dateString);

        if (empty($dateString)) {
            throw new \Exception("Date is required at row {$rowNumber}");
        }

        foreach (self::DATE_FORMATS as $format) {
            $date = \DateTime::createFromFormat($format, $dateString);
            if ($date && $date->format($format) === $dateString) {
                return $date->format('Y-m-d');
            }
        }

        throw new \Exception("Invalid date format at row {$rowNumber}: {$dateString}");
    }

    private function parseAmount(string $amountString): float
    {
        $amountString = trim($amountString);

        if (empty($amountString)) {
            return 0.0;
        }

        $amountString = str_replace([' ', "\u{00A0}"], '', $amountString);

        $isNegative = str_starts_with($amountString, '-') || str_starts_with($amountString, '(');
        $amountString = str_replace(['-', '(', ')'], '', $amountString);

        $hasCommaDecimal = preg_match('/,\d{1,2}$/', $amountString);
        $hasDotDecimal = preg_match('/\.\d{1,2}$/', $amountString);

        if ($hasCommaDecimal) {
            $amountString = str_replace('.', '', $amountString);
            $amountString = str_replace(',', '.', $amountString);
        } else {
            $amountString = str_replace(',', '', $amountString);
        }

        $amount = (float) $amountString;

        return $isNegative ? -$amount : $amount;
    }

    private function isEmptyRow(array $row): bool
    {
        return empty(array_filter($row, fn($cell) => !empty(trim($cell))));
    }

    private function normalizeString(string $str, string $encoding): string
    {
        if ($encoding !== 'UTF-8') {
            $str = mb_convert_encoding($str, 'UTF-8', $encoding);
        }

        return trim($str);
    }
}
