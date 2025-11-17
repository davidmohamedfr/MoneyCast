<?php

namespace App\Domain\Import\Repositories;

use App\Domain\Import\Data\ImportData;
use App\Domain\Import\Enums\ImportStatus;
use App\Domain\Import\Models\Import;
use Illuminate\Database\Eloquent\Collection;

class ImportRepository implements ImportRepositoryInterface
{
    public function findById(int $id, int $userId): ?Import
    {
        return Import::query()
            ->with(['account', 'importedRows'])
            ->byUser($userId)
            ->find($id);
    }

    public function findByUser(int $userId): Collection
    {
        return Import::query()
            ->with(['account'])
            ->byUser($userId)
            ->orderByDesc('created_at')
            ->get();
    }

    public function create(ImportData $data): Import
    {
        $import = Import::create([
            'user_id' => $data->user_id,
            'account_id' => $data->account_id,
            'source_type' => $data->source_type,
            'file_name' => $data->file_name,
            'file_path' => $data->file_path,
            'status' => ImportStatus::PENDING,
            'mapping' => $data->mapping,
        ]);

        return $import;
    }

    public function update(Import $import, array $attributes): Import
    {
        $import->update($attributes);
        return $import->fresh();
    }

    public function delete(Import $import): bool
    {
        if ($import->file_path) {
            \Illuminate\Support\Facades\Storage::disk('private')->delete($import->file_path);
        }

        return $import->delete();
    }
}
