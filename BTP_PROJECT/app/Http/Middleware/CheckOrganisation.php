<?php

namespace App\Http\Middleware;

use App\Models\Organisation;
use App\Models\OrganisationUser;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class CheckOrganisation
{
    public function handle(Request $request, Closure $next)
    {
        if (! Auth::check()) {
            return $next($request);
        }

        if ($request->routeIs('organisations.index')) {
            return $next($request);
        }

        $user = Auth::user();

        //  Vérifier directement dans organisation_users
        $organisationUsers = OrganisationUser::where('user_id', $user->id)->get();
        if ($organisationUsers->isEmpty()) {
            setPermissionsTeamId(null);
            $user->unsetRelation('roles')->unsetRelation('permissions');
            session()->forget(['active_organisation_id', 'active_organisation_name']);


            return $next($request);
        }

        //  Organisation système prioritaire
        $systemOrganisation = $organisationUsers->map->organisation->firstWhere('is_system', true);

        if ($systemOrganisation) {
            session([
                'active_organisation_id'   => $systemOrganisation->id,
                'active_organisation_name' => $systemOrganisation->nom,
            ]);

            setPermissionsTeamId($systemOrganisation->id);
            $user->unsetRelation('roles')->unsetRelation('permissions');

            return $next($request);
        }

        //  Organisation normale
        $organisationId = session('active_organisation_id');

        if (! $organisationId) {
            $organisation = $organisationUsers
                ->sortByDesc('created_at')
                ->first()
                ->organisation;

            if ($organisation) {
                session([
                    'active_organisation_id'   => $organisation->id,
                    'active_organisation_name' => $organisation->nom,
                ]);

                setPermissionsTeamId($organisation->id);
                $user->unsetRelation('roles')->unsetRelation('permissions');
            } else {
                // Aucun lien trouvé, on redirige
                return redirect()->route('organisations.index');
            }
        } else {
            //  Vérification stricte dans organisation_users
            $exists = OrganisationUser::where('organisation_id', $organisationId)
                ->where('user_id', $user->id)
                ->exists();

            if (! $exists) {
                session()->forget(['active_organisation_id', 'active_organisation_name']);
                return redirect()->route('organisations.index');
            }

            $organisation = Organisation::find($organisationId);

            setPermissionsTeamId($organisation->id);
            $user->unsetRelation('roles')->unsetRelation('permissions');
        }

        return $next($request);
    }
}
