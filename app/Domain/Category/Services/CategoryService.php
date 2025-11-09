<?php

namespace App\Domain\Category\Services;

use App\Domain\Category\Repositories\CategoryRepositoryInterface;

class CategoryService
{
    public function __construct(
        private CategoryRepositoryInterface $repository
    ) {}

    public function getAllCategoriesGroupedByType(): array
    {
        $categories = $this->repository->all();

        return [
            'income' => $categories->where('type', 'income')->values(),
            'expense' => $categories->where('type', 'expense')->values(),
        ];
    }
}
