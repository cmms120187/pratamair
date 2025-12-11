<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

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
        'nik',
        'name',
        'email',
        'password',
        'role',
        'atasan_id',
        'photo',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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
        ];
    }

    /**
     * Relasi ke atasan (self-referencing)
     */
    public function atasan()
    {
        return $this->belongsTo(User::class, 'atasan_id');
    }

    /**
     * Relasi ke bawahan
     */
    public function bawahan()
    {
        return $this->hasMany(User::class, 'atasan_id');
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole($role)
    {
        return $this->role === $role;
    }

    /**
     * Check if user has any of the given roles
     */
    public function hasAnyRole(array $roles)
    {
        return in_array($this->role, $roles);
    }

    /**
     * Check if user can access a menu
     */
    public function canAccessMenu($menuKey)
    {
        try {
            if (class_exists('\App\Helpers\PermissionHelper')) {
                return \App\Helpers\PermissionHelper::canAccessMenu($this->role ?? 'mekanik', $menuKey);
            }
        } catch (\Exception $e) {
            // If PermissionHelper not available, allow access (fallback)
        }
        // Fallback: allow all access if PermissionHelper not available
        return true;
    }

    /**
     * Check if user is admin
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user can manage users/permissions
     */
    public function canManageUsers()
    {
        try {
            if (class_exists('\App\Helpers\PermissionHelper')) {
                return \App\Helpers\PermissionHelper::canManageUsers($this->role ?? 'mekanik');
            }
        } catch (\Exception $e) {
            // If PermissionHelper not available, check if admin
        }
        // Fallback: only admin can manage users
        return $this->role === 'admin';
    }

    /**
     * Check if user role is higher or equal to another role
     */
    public function isRoleHigherOrEqual($compareRole)
    {
        try {
            if (class_exists('\App\Helpers\PermissionHelper')) {
                return \App\Helpers\PermissionHelper::isRoleHigherOrEqual($this->role ?? 'mekanik', $compareRole);
            }
        } catch (\Exception $e) {
            // If PermissionHelper not available, return false (safer)
        }
        // Fallback: return false if PermissionHelper not available
        return false;
    }
}
