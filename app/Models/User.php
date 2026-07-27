<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'user';
    protected $primaryKey = 'user_id';

    protected $fillable = [
        'username',
        'first_name',
        'last_name',
        'middle_name',
        'date_of_birth',
        'email_account',
        'email_hash',
        'contact_number',
        'role',
        'password',
        'totp_secret',
    ];

    /**
     * AES-256 encryption for PII fields (via APP_KEY) and bcrypt for password.
     * email_account is encrypted at rest; email_hash (SHA-256) is used for
     * indexed DB lookups so WHERE queries still work correctly.
     */
    protected $casts = [
        'first_name'    => 'encrypted',
        'last_name'     => 'encrypted',
        'middle_name'   => 'encrypted',
        'email_account' => 'encrypted',
        'password'      => 'hashed',
    ];

    // email_hash is a server-side lookup key — never expose it in responses.
    protected $hidden = ['password', 'totp_secret', 'email_hash'];

    public $timestamps = false;

    // ── Relationships ────────────────────────────────────────────
    public function client()
    {
        return $this->hasOne(Client::class, 'user_id', 'user_id');
    }

    public function staff()
    {
        return $this->hasOne(Staff::class, 'user_id', 'user_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id', 'user_id');
    }

    public function userLogs()
    {
        return $this->hasMany(UserLog::class, 'user_id', 'user_id');
    }

    public function latestLog()
    {
        return $this->hasOne(UserLog::class, 'user_id', 'user_id')->latestOfMany('created_at');
    }

    // ── Helpers ──────────────────────────────────────────────────
    public function isAdmin(): bool   { return $this->role === 'admin'; }
    public function isClient(): bool  { return $this->role === 'client'; }
    public function isWorker(): bool  { return $this->role === 'worker'; }
    public function isStaff(): bool   { return in_array($this->role, ['admin', 'staff']); }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->middle_name} {$this->last_name}");
    }
}
