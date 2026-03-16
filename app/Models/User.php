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
        'alert_email',
        'password',
        'pin',
        'last_pin_login_at',
        'role',
        'branch_id',
        'is_active',
        'must_change_password',
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
        'pin',
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
            'last_pin_login_at' => 'datetime',
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

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
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

    /**
     * Set or update the user's PIN.
     */
    public function setPin(string $pin): void
    {
        $this->pin = bcrypt($pin);
        $this->save();
    }

    /**
     * Verify the user's PIN.
     */
    public function verifyPin(string $pin): bool
    {
        if (!$this->pin) {
            return false;
        }
        return \Hash::check($pin, $this->pin);
    }

    /**
     * Record PIN login.
     */
    public function recordPinLogin(): void
    {
        $this->last_pin_login_at = now();
        $this->save();
    }

    /**
     * Get the user's attendance records.
     */
    public function attendance(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(StaffAttendance::class);
    }

    /**
     * Get permission overrides requested by this user.
     */
    public function permissionOverridesRequested(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PermissionOverride::class, 'requested_by');
    }

    /**
     * Get permission overrides approved/denied by this user.
     */
    public function permissionOverridesApproved(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PermissionOverride::class, 'approved_by');
    }

    /**
     * Check if user is currently clocked in.
     */
    public function isClockedIn(): bool
    {
        return StaffAttendance::isUserClockedIn($this->id);
    }

    /**
     * Check if user can approve permission overrides.
     */
    public function canApproveOverrides(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Check if user can perform a restricted action.
     */
    public function canPerformRestrictedAction(string $action): bool
    {
        return PermissionOverride::canPerformAction($this, $action);
    }
}
