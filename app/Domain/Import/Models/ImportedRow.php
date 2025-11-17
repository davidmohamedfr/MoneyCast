<?php

namespace App\Domain\Import\Models;

use App\Domain\Transaction\Models\Transaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportedRow extends Model
{
    protected $fillable = [
        'import_id',
        'row_number',
        'status',
        'raw_data',
        'transaction_id',
        'error_message',
    ];

    protected $casts = [
        'raw_data' => 'array',
        'row_number' => 'integer',
    ];

    public function import(): BelongsTo
    {
        return $this->belongsTo(Import::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
