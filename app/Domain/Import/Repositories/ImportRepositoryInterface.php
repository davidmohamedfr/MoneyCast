<?php

namespace App\Domain\Import\Repositories;

use App\Domain\Import\Data\ImportData;
use App\Domain\Import\Models\Import;
use Illuminate\Database\Eloquent\Collection;

interface ImportRepositoryInterface
{
    public function findById(int $id, int $userId): ?Import;

    public function findByUser(int $userId): Collection;

    public function create(ImportData $data): Import;

    public function update(Import $import, array $attributes): Import;

    public function delete(Import $import): bool;
}
