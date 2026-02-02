<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'branch_id',
        'is_active',
        'qr_token',
        'qr_token_generated_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'qr_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'qr_token_generated_at' => 'datetime',
        ];
    }

    public function branch(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function sales(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function activityLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserActivityLog::class);
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isSupervisor(): bool
    {
        return $this->role === 'supervisor';
    }

    public function isCashier(): bool
    {
        return $this->role === 'cashier';
    }

    /**
     * Generate a new QR token for the user.
     */
    public function generateQrToken(): string
    {
        $this->qr_token = Str::random(64);
        $this->qr_token_generated_at = now();
        $this->save();

        return $this->qr_token;
    }
}
