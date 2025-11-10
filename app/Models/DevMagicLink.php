<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class DevMagicLink extends Model
{
    protected $fillable = [
        'user_id',
        'token',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        if (app()->environment('production')) {
            throw new \RuntimeException(
                'DevMagicLink model cannot be used in production environment. '.
                'This is a development-only feature for testing purposes.'
            );
        }
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function generateForUser(User $user, int $expiresInMinutes = 60): self
    {
        $magicLink = new self();
        $magicLink->user_id = $user->id;
        $magicLink->token = Str::random(64);
        $magicLink->expires_at = now()->addMinutes($expiresInMinutes);
        $magicLink->save();

        return $magicLink;
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isValid(): bool
    {
        return !$this->isExpired();
    }

    public static function findByToken(string $token): ?self
    {
        return self::where('token', $token)->first();
    }

    public function getUrl(): string
    {
        return url("/dev/auth/magic/{$this->token}");
    }
}
