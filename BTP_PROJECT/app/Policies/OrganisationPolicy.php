<?php

namespace App\Policies;

use App\Models\User;

class OrganisationPolicy
{
    /**
     * Exécute une vérification dans le contexte d'une organisation.
     *
     * @param  \App\Models\User  $user
     * @param  int  $organisationId
     * @param  callable  $callback
     * @return bool
     */
    protected function runInOrganisationContext(User $user, int $organisationId, callable $callback): bool
    {
        $currentTeamId = getPermissionsTeamId();

        // Scope sur l'organisation
        setPermissionsTeamId($organisationId);

        // Purge du cache des relations
        $user->unsetRelation('roles')->unsetRelation('permissions');

        // Exécution de la logique (permission ou rôle)
        $result = $callback($user);

        // Restauration du contexte initial
        setPermissionsTeamId($currentTeamId);
        $user->unsetRelation('roles')->unsetRelation('permissions');

        return $result;
    }

    /**
     * Vérifie si l'utilisateur a une permission donnée
     * dans le contexte d'une organisation.
     */
    public function checkPermission(User $user, int $organisationId, string $permission): bool
    {
        return $this->runInOrganisationContext($user, $organisationId, fn ($u) => $u->hasPermissionTo($permission));
    }

    /**
     * Vérifie si l'utilisateur a un rôle donné
     * dans le contexte d'une organisation.
     */
    public function checkRole(User $user, int $organisationId, string $role): bool
    {
        return $this->runInOrganisationContext($user, $organisationId, fn ($u) => $u->hasRole($role));
    }
}
