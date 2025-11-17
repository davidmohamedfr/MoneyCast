<?php

namespace App\Domain\Import\Events;

use App\Domain\Import\Models\Import;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ImportProgressUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Import $import,
        public int $importedRows,
        public int $totalRows
    ) {}

    public function toArray(): array
    {
        return [
            'import_id' => $this->import->id,
            'status' => $this->import->status->value,
            'imported_rows' => $this->importedRows,
            'total_rows' => $this->totalRows,
            'progress' => $this->totalRows > 0 ? round(($this->importedRows / $this->totalRows) * 100) : 0,
        ];
    }
}
