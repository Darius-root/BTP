<?php

namespace App\Services;

use App\Models\User;

class OrganisationContext
{
    /**
     * Vérifie si l'utilisateur possède un rôle donné
     * dans le contexte d'une organisation spécifique.
     */
    public static function hasRole(User $user, int $organisationId, string $role): bool
    {
        return self::run($user, $organisationId, fn ($u) => $u->hasRole($role));
    }

    /**
     * Vérifie si l'utilisateur possède une permission donnée
     * dans le contexte d'une organisation spécifique.
     */
    public static function hasPermission(User $user, int $organisationId, string $permission): bool
    {
        return self::run($user, $organisationId, fn ($u) => $u->hasPermissionTo($permission));
    }

    /**
     * Exécute une vérification dans le scope d'une organisation.
     *
     * @param  \App\Models\User  $user
     * @param  int  $organisationId
     * @param  callable  $callback
     * @return bool
     */
    protected static function run(User $user, int $organisationId, callable $callback): bool
    {
        if (! $organisationId) {
            return false;
        }

        // Sauvegarder le contexte actuel
        $currentTeamId = getPermissionsTeamId();

        // Changer de scope
        setPermissionsTeamId($organisationId);

        // Purger le cache des relations
        $user->unsetRelation('roles')->unsetRelation('permissions');

        // Exécuter la logique (permission ou rôle)
        $result = $callback($user);

        // Restaurer le contexte initial
        setPermissionsTeamId($currentTeamId);
        $user->unsetRelation('roles')->unsetRelation('permissions');

        return $result;
    }
}
