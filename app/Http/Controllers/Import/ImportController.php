<?php

namespace App\Http\Controllers\Import;

use App\Domain\Account\Repositories\AccountRepositoryInterface;
use App\Domain\Import\Data\ImportData;
use App\Domain\Import\Enums\ImportSource;
use App\Domain\Import\Models\Import;
use App\Domain\Import\Repositories\ImportRepositoryInterface;
use App\Domain\Import\Services\ImportService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Import\StoreImportRequest;
use App\Http\Requests\Import\UpdateMappingRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ImportController extends Controller
{
    public function __construct(
        private ImportService $importService,
        private ImportRepositoryInterface $importRepository,
        private AccountRepositoryInterface $accountRepository
    ) {}

    public function index(): Response
    {
        $this->authorize('viewAny', Import::class);

        $imports = $this->importRepository->findByUser(auth()->id());

        return Inertia::render('import/Index', [
            'imports' => $imports,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Import::class);

        $accounts = $this->accountRepository->getAllForUser(auth()->id());

        return Inertia::render('import/Create', [
            'accounts' => $accounts,
        ]);
    }

    public function store(StoreImportRequest $request): RedirectResponse
    {
        try {
            $file = $request->file('file');

            \Illuminate\Support\Facades\Log::info('Import upload started', [
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
            ]);

            $filePath = $file->store('imports', 'private');

            \Illuminate\Support\Facades\Log::info('File stored successfully', [
                'file_path' => $filePath,
            ]);

            $importData = new ImportData(
                user_id: auth()->id(),
                account_id: $request->input('account_id'),
                source_type: ImportSource::from($request->input('source_type')),
                file_name: $file->getClientOriginalName(),
                file_path: $filePath
            );

            $import = $this->importService->initiateImport($importData);

            \Illuminate\Support\Facades\Log::info('Import record created', [
                'import_id' => $import->id,
            ]);

            try {
                $parsedData = $this->importService->parseFile($import);

                \Illuminate\Support\Facades\Log::info('File parsed successfully', [
                    'import_id' => $import->id,
                    'total_rows' => $parsedData->total_rows,
                ]);

                session()->put("import_{$import->id}_parsed_data", serialize($parsedData));

                return redirect()->route('imports.show', $import->id)
                    ->with('success', 'File uploaded and parsed successfully. Please map the fields.');
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to parse import file', [
                    'import_id' => $import->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                $this->importRepository->delete($import);

                return redirect()->route('imports.create')
                    ->with('error', 'Failed to parse file: ' . $e->getMessage());
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to store import file', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('imports.create')
                ->with('error', 'Failed to upload file: ' . $e->getMessage());
        }
    }

    public function show(Import $import): Response
    {
        $this->authorize('view', $import);

        $import->load(['account']);

        $parsedData = null;
        if (session()->has("import_{$import->id}_parsed_data")) {
            $parsedData = unserialize(session()->get("import_{$import->id}_parsed_data"));
        }

        return Inertia::render('import/Show', [
            'importData' => $import,
            'parsedData' => $parsedData,
        ]);
    }

    public function update(UpdateMappingRequest $request, Import $import): RedirectResponse
    {
        $mapping = $request->input('mapping');

        try {
            $parsedData = $this->importService->parseFile($import, $mapping);

            $importData = new ImportData(
                user_id: $import->user_id,
                account_id: $import->account_id,
                source_type: $import->source_type,
                file_name: $import->file_name,
                file_path: $import->file_path,
                mapping: $mapping
            );

            $errors = $this->importService->validateImport($import, $importData, $parsedData);

            if (!empty($errors)) {
                return redirect()->route('imports.show', $import->id)
                    ->with('error', 'Validation failed. Please check the errors.')
                    ->with('validation_errors', $errors);
            }

            $this->importService->queueImport($import);

            session()->forget("import_{$import->id}_parsed_data");

            return redirect()->route('imports.show', $import->id)
                ->with('success', 'Import processing started. Please wait...');
        } catch (\Exception $e) {
            return redirect()->route('imports.show', $import->id)
                ->with('error', 'Failed to process import: ' . $e->getMessage());
        }
    }

    public function destroy(Import $import): RedirectResponse
    {
        $this->authorize('delete', $import);

        $this->importRepository->delete($import);

        session()->forget("import_{$import->id}_parsed_data");

        return redirect()->route('imports.index')
            ->with('success', 'Import deleted successfully.');
    }
}
