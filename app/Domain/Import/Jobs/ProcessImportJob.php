<?php

namespace App\Domain\Import\Jobs;

use App\Domain\Import\Enums\ImportStatus;
use App\Domain\Import\Models\Import;
use App\Domain\Import\Services\ImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;
    public $timeout = 300;

    public function __construct(
        public int $importId
    ) {
        $this->onQueue('default');
    }

    public function handle(ImportService $service): void
    {
        $import = Import::find($this->importId);

        if (!$import) {
            return;
        }

        try {
            $service->processImport($import);
        } catch (\Exception $e) {
            $import->update([
                'status' => ImportStatus::FAILED,
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
