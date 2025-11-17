<?php

namespace App\Domain\Import\Services;

use App\Domain\Import\Actions\ProcessImportAction;
use App\Domain\Import\Actions\ValidateImportAction;
use App\Domain\Import\Data\ImportData;
use App\Domain\Import\Data\ImportSummaryData;
use App\Domain\Import\Data\ParsedImportData;
use App\Domain\Import\Enums\ImportStatus;
use App\Domain\Import\Jobs\ProcessImportJob;
use App\Domain\Import\Models\Import;
use App\Domain\Import\Parsers\ParserFactory;
use App\Domain\Import\Repositories\ImportRepositoryInterface;

class ImportService
{
    public function __construct(
        private ImportRepositoryInterface $importRepository,
        private ParserFactory $parserFactory,
        private DuplicateDetectionService $duplicateDetectionService,
        private ValidateImportAction $validateImportAction,
        private ProcessImportAction $processImportAction
    ) {}

    public function initiateImport(ImportData $data): Import
    {
        return $this->importRepository->create($data);
    }

    public function parseFile(Import $import, ?array $mapping = null): ParsedImportData
    {
        try {
            $parser = $this->parserFactory->make($import->source_type);

            \Illuminate\Support\Facades\Log::info('Parser created', [
                'import_id' => $import->id,
                'source_type' => $import->source_type->value,
                'parser_class' => get_class($parser),
            ]);

            $filePath = \Illuminate\Support\Facades\Storage::disk('private')->path($import->file_path);

            \Illuminate\Support\Facades\Log::info('File path resolved', [
                'import_id' => $import->id,
                'file_path' => $filePath,
                'file_exists' => file_exists($filePath),
                'file_readable' => file_exists($filePath) ? is_readable($filePath) : false,
            ]);

            $parsedData = $parser->parse(
                $filePath,
                $mapping
            );

            \Illuminate\Support\Facades\Log::info('File parsed', [
                'import_id' => $import->id,
                'total_rows' => $parsedData->total_rows,
                'detected_columns' => $parsedData->detected_columns,
            ]);

            $this->importRepository->update($import, [
                'status' => ImportStatus::MAPPING,
                'total_rows' => $parsedData->total_rows,
                'mapping' => $parsedData->detected_columns,
            ]);

            return $parsedData;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to parse file in ImportService', [
                'import_id' => $import->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    public function validateImport(Import $import, ImportData $importData, ParsedImportData $parsedData): array
    {
        $this->importRepository->update($import, [
            'status' => ImportStatus::VALIDATING,
        ]);

        $errors = $this->validateImportAction->execute($importData, $parsedData);

        return $errors;
    }

    public function queueImport(Import $import): void
    {
        $this->importRepository->update($import, [
            'status' => ImportStatus::PROCESSING,
        ]);

        ProcessImportJob::dispatch($import->id);
    }

    public function processImport(Import $import): ImportSummaryData
    {
        // Re-parse the file with the stored mapping
        $parsedData = $this->parseFile($import, $import->mapping);

        return $this->processImportAction->execute($import, $parsedData);
    }
}
