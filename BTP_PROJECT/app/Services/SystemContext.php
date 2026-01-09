<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;

class SystemContext
{
    /**
     * Vérifie si un utilisateur a une permission système spécifique
     *
     * @param \App\Models\User|null $user
     * @param string $permission
     * @return bool
     */
    public static function hasPermission($user, string $permission): bool
    {
        if (!$user) {
            return false;
        }

        // Si tu utilises spatie/permission
        if (method_exists($user, 'hasPermissionTo')) {
            return $user->hasPermissionTo($permission);
        }

        // Sinon, on vérifie dans l'attribut "permissions" ou une collection
        $permissions = [];

        if (isset($user->permissions)) {
            if (is_array($user->permissions)) {
                $permissions = $user->permissions;
            } elseif (method_exists($user->permissions, 'toArray')) {
                $permissions = $user->permissions->toArray();
            }
        }

        return in_array($permission, $permissions);
    }

    /**
     * Raccourci pour vérifier la permission de l'utilisateur connecté
     *
     * @param string $permission
     * @return bool
     */
    public static function currentUserHasPermission(string $permission): bool
    {
        return self::hasPermission(Auth::user(), $permission);
    }
}
