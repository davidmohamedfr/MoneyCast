<?php

namespace App\Domain\Import\Policies;

use App\Domain\Import\Enums\ImportStatus;
use App\Domain\Import\Models\Import;
use App\Models\User;

class ImportPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Import $import): bool
    {
        return $user->id === $import->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Import $import): bool
    {
        if ($user->id !== $import->user_id) {
            return false;
        }

        return in_array($import->status, [ImportStatus::PENDING, ImportStatus::MAPPING]);
    }

    public function delete(User $user, Import $import): bool
    {
        return $user->id === $import->user_id;
    }
}
