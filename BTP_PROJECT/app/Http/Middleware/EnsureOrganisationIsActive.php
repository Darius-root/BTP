<?php

namespace App\Http\Middleware;

use App\Models\Organisation;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureOrganisationIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        /** @var Organisation|null $organisation */
        $organisation = session('active_organisation');

        /*
        |--------------------------------------------------------------------------
        | 1. Aucune organisation active
        |--------------------------------------------------------------------------
        */
        if (! $organisation instanceof Organisation) {
            session()->forget('active_organisation');

            return redirect()
                ->route('organisations.index')
                ->with('error', 'Veuillez activer une organisation avant de continuer.');
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Organisation supprimée / invalide
        |--------------------------------------------------------------------------
        */
        if (! Organisation::whereKey($organisation->id)->exists()) {
            session()->forget('active_organisation');

            return redirect()
                ->route('organisations.index')
                ->with('error', 'Organisation invalide ou supprimée.');
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Organisation système
        |--------------------------------------------------------------------------
        */
        if ($organisation->is_system) {

            // Contexte Spatie → organisation système
            setPermissionsTeamId($organisation->id);
            $user->unsetRelation('roles')->unsetRelation('permissions');

            if (! $user->hasRole('SYSTEM_ADMIN_PLATEFORME')) {
                return redirect()
                    ->route('organisations.index')
                    ->with('error', 'Accès réservé aux super administrateurs.');
            }

            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | 4. Organisation normale
        |--------------------------------------------------------------------------
        */

        // L’utilisateur n’appartient pas à cette organisation
        if (! $user->organisations()->whereKey($organisation->id)->exists()) {
            session()->forget('active_organisation');

            return redirect()
                ->route('organisations.index')
                ->with('error', 'Vous n’appartenez pas à cette organisation.');
        }

        // Contexte Spatie → organisation normale
        setPermissionsTeamId($organisation->id);
        $user->unsetRelation('roles')->unsetRelation('permissions');

        return $next($request);
    }
}
