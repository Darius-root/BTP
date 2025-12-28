<?php

namespace App\Policies;

use App\Models\User;

class RolePolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        
    }


       public function createSystemRole(User $user)
    {
        return $user->hasRole('ADMIN_PLATEFORME');
    }

    public function createOrganisationRole(User $user)
    {
        return $user->hasRole('ADMIN_PLATEFORME');
    }

    public function assignSystemRole(User $user)
    {
        return $user->hasRole('ADMIN_PLATEFORME');
    }

    public function viewAllOrganisations(User $user)
    {
        return $user->hasRole('ADMIN_PLATEFORME');
    }
}
