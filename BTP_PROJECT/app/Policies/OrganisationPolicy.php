<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Organisation;

class OrganisationPolicy
{
    /**
     * Vérifie si l'utilisateur a une permission donnée
     * dans le contexte d'une organisation.
     */
    public function checkPermission(User $user, int $organisationId, string $permission): bool
    {
        $currentTeamId = getPermissionsTeamId();

        // Scope sur l'organisation
        setPermissionsTeamId($organisationId);

        // Recharge les relations pour éviter le cache
        $user->unsetRelation('roles')->unsetRelation('permissions');

        $result = $user->can($permission);

        // Toujours restaurer le team initial
        setPermissionsTeamId($currentTeamId);
        $user->unsetRelation('roles')->unsetRelation('permissions');

        return $result;
    }
}
