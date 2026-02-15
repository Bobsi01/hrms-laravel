<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    /**
     * Our DB uses 'password_hash' instead of Laravel's default 'password'.
     * This tells the Authenticatable trait (getAuthPassword, rehashPasswordIfRequired, etc.)
     * to use the correct column name.
     */
    protected $authPasswordName = 'password_hash';

    protected $fillable = [
        'email',
        'password_hash',
        'full_name',
        'role',
        'status',
        'branch_id',
        'is_system_admin',
        'last_login',
    ];

    protected $hidden = [
        'password_hash',
    ];

    protected function casts(): array
    {
        return [
            'last_login' => 'datetime',
            'is_system_admin' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    // ─── Relationships ───────────────────────────────

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function rememberTokens(): HasMany
    {
        return $this->hasMany(UserRememberToken::class);
    }

    // ─── Accessors ───────────────────────────────────

    public function isSuperadmin(): bool
    {
        $superadminId = config('hrms.superadmin.user_id');
        $superadminEmail = config('hrms.superadmin.email');

        return ($superadminId > 0 && $this->id === $superadminId)
            || $this->email === $superadminEmail;
    }

    public function isSystemAdmin(): bool
    {
        return $this->is_system_admin || $this->isSuperadmin();
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->full_name ?: $this->email;
    }

    // ─── Remember Token (DB has no remember_token column) ────

    public function getRememberToken(): ?string
    {
        return null;
    }

    public function setRememberToken($value): void
    {
        // No-op: original app uses user_remember_tokens table
    }

    public function getRememberTokenName(): string
    {
        return '';
    }
}
