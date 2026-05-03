<?php

namespace App\Models;

use App\Support\RolePermissions;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'mobile',
        'password',
        'role',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ── Filament access ────────────────────────────────────────────

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isBackOffice();
    }

    // ── Permission check ───────────────────────────────────────────

    /**
     * Check if this user has a specific Waterfall permission.
     * Overrides Laravel's can() for string permissions.
     */
    public function can($abilities, $arguments = []): bool
    {
        // For string permissions, use our role matrix
        if (is_string($abilities)) {
            return RolePermissions::roleHas($this->role ?? '', $abilities);
        }

        return parent::can($abilities, $arguments);
    }

    // ── Role helpers ───────────────────────────────────────────────

    public const ROLES = [
        'super_admin' => 'Super Admin',
        'admin' => 'Admin',
        'delivery_manager' => 'Delivery Manager',
        'billing_officer' => 'Billing Officer',
        'stock_manager' => 'Stock Manager',
        'customer' => 'Customer',
        'dealer' => 'Dealer',
        'delivery_staff' => 'Delivery Staff',
    ];

    public const BACK_OFFICE_ROLES = [
        'super_admin',
        'admin',
        'delivery_manager',
        'billing_officer',
        'stock_manager',
    ];

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    public function isBackOffice(): bool
    {
        return in_array($this->role, self::BACK_OFFICE_ROLES);
    }

    public function scopeRole(Builder $query, string $role): Builder
    {
        return $query->where('role', $role);
    }

    public function scopeBackOffice(Builder $query): Builder
    {
        return $query->whereIn('role', self::BACK_OFFICE_ROLES);
    }

    public function scopeDeliveryStaff(Builder $query): Builder
    {
        return $query->where('role', 'delivery_staff');
    }

    // ── Relationships ──────────────────────────────────────────────

    public function customer(): HasOne
    {
        return $this->hasOne(Customer::class);
    }

    public function dealer(): HasOne
    {
        return $this->hasOne(Dealer::class);
    }

    public function managedZones(): HasMany
    {
        return $this->hasMany(Zone::class, 'delivery_manager_id');
    }

    /**
     * Many-to-many relationship: User can be assigned to multiple zones
     * This is used for delivery managers who manage multiple zones
     */
    public function zones(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Zone::class, 'user_zone')
            ->withTimestamps();
    }

    /**
     * Get all zones for this user (for API response)
     * Returns array of zone names
     */
    public function getAllZones(): array
    {
        // For delivery managers, get zones from many-to-many relationship
        if ($this->role === 'delivery_manager') {
            $zones = $this->zones()->pluck('name')->toArray();
            
            // Fallback to managedZones if no zones assigned via pivot table
            if (empty($zones)) {
                $zones = $this->managedZones()->pluck('name')->toArray();
            }
            
            return $zones;
        }
        
        // For delivery staff, get zone from their latest delivery
        if ($this->role === 'delivery_staff') {
            $zone = $this->assignedDeliveries()
                ->with('zone')
                ->latest('assigned_at')
                ->first()?->zone;
            
            return $zone ? [$zone->name] : [];
        }
        
        return [];
    }

    public function orderedOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'ordered_by');
    }

    public function assignedDeliveries(): HasMany
    {
        return $this->hasMany(Delivery::class, 'delivery_staff_id');
    }

    public function currentActiveDelivery(): HasOne
    {
        return $this->hasOne(Delivery::class, 'delivery_staff_id')
            ->whereNotIn('delivery_status', [
                'cancelled',
                'delivered',
                'partial_delivered',
                'not_delivered',
                'customer_unavailable',
                'failed',
            ])
            ->latestOfMany('assigned_at');
    }

    public function latestLocation(): HasOne
    {
        return $this->hasOne(DeliveryStaffLocation::class);
    }

    public function fcmTokens(): HasMany
    {
        return $this->hasMany(UserFcmToken::class);
    }

    public function deliveryAssignmentsMade(): HasMany
    {
        return $this->hasMany(Delivery::class, 'assigned_by');
    }

    public function receivedPayments(): HasMany
    {
        return $this->hasMany(Payment::class, 'received_by');
    }

    public function createdInvoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'created_by');
    }

    public function createdSubscriptions(): HasMany
    {
        return $this->hasMany(CustomerSubscription::class, 'created_by');
    }

    public function updatedSubscriptions(): HasMany
    {
        return $this->hasMany(CustomerSubscription::class, 'updated_by');
    }
}
