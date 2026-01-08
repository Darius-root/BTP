<?php

namespace App\Http\Middleware;

use App\Models\Organisation;
use App\Models\OrganisationUser;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureOrganisationIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        /*
        |--------------------------------------------------------------------------
        | 1. Récupération de l’ID d’organisation depuis la session
        |--------------------------------------------------------------------------
        */
        $organisationId = session('active_organisation_id');

        if (! $organisationId) {
            session()->forget(['active_organisation_id', 'active_organisation_name']);

            return redirect()
                ->route('organisations.index')
                ->with('error', 'Veuillez activer une organisation avant de continuer.');
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Chargement de l’organisation
        |--------------------------------------------------------------------------
        */
        $organisation = Organisation::find($organisationId);

        if (! $organisation) {
            session()->forget(['active_organisation_id', 'active_organisation_name']);

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

            setPermissionsTeamId($organisation->id);
            $user->unsetRelation('roles')->unsetRelation('permissions');

            if (! $user->hasRole('SYSTEM_ADMIN_PLATEFORME')) {
                session()->forget(['active_organisation_id', 'active_organisation_name']);

                return redirect()
                    ->route('organisations.index')
                    ->with('error', 'Accès refusé');
            }

            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | 4. Organisation normale → vérifier appartenance
        |--------------------------------------------------------------------------
        */
        $belongsToOrg = OrganisationUser::where('organisation_id', $organisation->id)
            ->where('user_id', $user->id)
            ->exists();

        if (! $belongsToOrg) {
            session()->forget(['active_organisation_id', 'active_organisation_name']);

            return redirect()
                ->route('organisations.index')
                ->with('error', 'Vous n’appartenez pas à cette organisation.');
        }

        /*
        |--------------------------------------------------------------------------
        | 5. Initialisation du contexte Spatie
        |--------------------------------------------------------------------------
        */
        setPermissionsTeamId($organisation->id);
        $user->unsetRelation('roles')->unsetRelation('permissions');

        return $next($request);
    }
}
