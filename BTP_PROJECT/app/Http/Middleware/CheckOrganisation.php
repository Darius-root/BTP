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
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        if (! Auth::check()) {
            return $next($request);
        }

        if ($request->routeIs('organisations.index')) {
            return $next($request);
        }

        $user = Auth::user();
        $organisations = $user->organisations()->get();
        if ($organisations->isEmpty()) {

            setPermissionsTeamId(null);
            $user->unsetRelation('roles')->unsetRelation('permissions');
            session()->forget([
                'active_organisation_id',
                'active_organisation_name'
            ]);

            return redirect()->route('organisations.index');
        }

        // 1️Organisation système prioritaire
        $systemOrganisation = $organisations->firstWhere('is_system', true);

        if ($systemOrganisation) {
            session([
                'active_organisation_id'   => $systemOrganisation->id,
                'active_organisation_name' => $systemOrganisation->nom,
            ]);

            setPermissionsTeamId($systemOrganisation->id);
            $user->unsetRelation('roles')->unsetRelation('permissions');

            return $next($request);
        }

        // 2️ Organisation normale
        $organisationId = session('active_organisation_id');
        if (! $organisationId) {
           
            if ($organisations->count() === 1) {
                $organisation = $organisations->first();

                session([
                    'active_organisation_id'   => $organisation->id,
                    'active_organisation_name' => $organisation->nom,
                ]);

                setPermissionsTeamId($organisation->id);
                $user->unsetRelation('roles')->unsetRelation('permissions');
            } else {
                return redirect()->route('organisations.index');
            }
        } else {

            $exists = OrganisationUser::where('organisation_id', $organisationId)
                ->where('user_id', $user->id)
                ->exists();

            if (! $exists) {
                session()->forget([
                    'active_organisation_id',
                    'active_organisation_name',
                ]);

                return redirect()->route('organisations.index');
            }

            $organisation = Organisation::find($organisationId);

            setPermissionsTeamId($organisation->id);
            $user->unsetRelation('roles')->unsetRelation('permissions');
            $user->unsetRelation('roles')->unsetRelation('permissions');
        }

        return $next($request);
    }
}
