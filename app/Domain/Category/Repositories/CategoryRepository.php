<?php

namespace App\Domain\Category\Repositories;

use App\Domain\Category\Models\Category;
use Illuminate\Database\Eloquent\Collection;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function all(): Collection
    {
        return Category::all();
    }

    public function findById(int $id): ?Category
    {
        return Category::find($id);
    }

    public function getByType(string $type): Collection
    {
        return Category::where('type', $type)->get();
    }
}
