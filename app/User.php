<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Log;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username',
        'name',
        'email',
        'password',
        'role', // Keeping old enum for backward compatibility or migration
        'role_id',
        'status',
        'super_admin',
        'commission_rate'
    ];

    /**
     * Get the user's assigned role
     */
    public function assignedRole()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Check if user has a specific permission
     */
    public function hasPermission($permission)
    {
        if (!$this->assignedRole) {
            return false;
        }

        // Super Admin bypass (optional, but good practice)
        if ($this->assignedRole->slug === 'super-admin') {
            return true;
        }

        Log::debug($this->assignedRole->permissions);

        return $this->assignedRole->permissions->contains('slug', $permission);
    }

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Check if user is super admin
     */
    public function isSuperAdmin()
    {
        return $this->role === 'super-admin';
    }

    /**
     * Check if user is admin
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is agent
     */
    public function isAgent()
    {
        return $this->role === 'agent';
    }

    /**
     * Get customers assigned to this agent
     */
    public function customers()
    {
        return $this->hasMany(Customer::class, 'agent_id');
    }

    /**
     * Get transactions for this agent
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'created_by');
    }

    // --- Permission Helpers ---

    public function isSuperAdminUser()
    {
        return $this->super_admin == 1;
    }

    public function isSuperAdminRole()
    {
        return $this->assignedRole && $this->assignedRole->slug === 'super-admin';
    }

    public function isAdminRole()
    {
        return $this->assignedRole && $this->assignedRole->slug === 'admin';
    }

    public function canManageUser($targetUser)
    {
        // Self management (Edit only, Delete handled in controller)
        if ($this->id === $targetUser->id) {
            return true;
        }

        // Super Admin User (Column = 1) can manage everyone
        if ($this->isSuperAdminUser()) {
            return true;
        }

        // Super Admin Role
        if ($this->isSuperAdminRole()) {
            // Cannot manage Super Admin User or other Super Admin Roles
            if ($targetUser->isSuperAdminUser() || $targetUser->isSuperAdminRole()) {
                return false;
            }
            return true; // Can manage Admin and Agent roles
        }

        // Admin Role
        if ($this->isAdminRole()) {
            // Can only manage Agents
            if ($targetUser->isSuperAdminUser() || $targetUser->isSuperAdminRole() || $targetUser->isAdminRole()) {
                return false;
            }
            return true;
        }

        return false;
    }

    public function allowedRoles()
    {
        if ($this->isSuperAdminUser()) {
            return Role::all();
        }

        if ($this->isSuperAdminRole()) {
            return Role::where('slug', '!=', 'super-admin')->get();
        }

        if ($this->isAdminRole()) {
            return Role::where('slug', 'agent')->get();
        }

        return collect([]);
    }
}
