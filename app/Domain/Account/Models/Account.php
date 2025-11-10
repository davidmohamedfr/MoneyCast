<?php

namespace App\Domain\Account\Models;

use App\Domain\Account\Enums\AccountType;
use App\Domain\Transaction\Models\Transaction;
use App\Models\User;
use Database\Factories\AccountFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The name of the "deleted at" column.
     *
     * @var string
     */
    const DELETED_AT = 'archived_at';

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'initial_balance',
        'currency',
        'bank',
    ];

    protected $casts = [
        'initial_balance' => 'decimal:4',
        'type' => AccountType::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    protected static function newFactory()
    {
        return AccountFactory::new();
    }
}
