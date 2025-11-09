<?php

namespace App\Domain\Category\Repositories;

use App\Domain\Category\Models\Category;
use Illuminate\Database\Eloquent\Collection;

interface CategoryRepositoryInterface
{
    public function all(): Collection;

    public function findById(int $id): ?Category;

    public function getByType(string $type): Collection;
}
