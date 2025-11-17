<?php

namespace App\Domain\Import\Models;

use App\Domain\Account\Models\Account;
use App\Domain\Import\Enums\ImportSource;
use App\Domain\Import\Enums\ImportStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Import extends Model
{
    protected $fillable = [
        'user_id',
        'account_id',
        'source_type',
        'file_name',
        'file_path',
        'status',
        'total_rows',
        'imported_rows',
        'skipped_rows',
        'failed_rows',
        'mapping',
        'error_message',
        'completed_at',
        'debug_logs',
    ];

    protected $casts = [
        'status' => ImportStatus::class,
        'source_type' => ImportSource::class,
        'mapping' => 'array',
        'total_rows' => 'integer',
        'imported_rows' => 'integer',
        'skipped_rows' => 'integer',
        'failed_rows' => 'integer',
        'completed_at' => 'datetime',
        'debug_logs' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function importedRows(): HasMany
    {
        return $this->hasMany(ImportedRow::class);
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByStatus($query, ImportStatus $status)
    {
        return $query->where('status', $status);
    }
}
