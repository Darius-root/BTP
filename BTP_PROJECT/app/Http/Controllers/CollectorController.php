<?php

namespace App\Http\Controllers;

use App\Models\OrganisationUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class CollectorController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $organisationId = getPermissionsTeamId();

        if ($organisationId === null) {
            abort(403, 'Aucune organisation active sélectionnée.');
        }

        setPermissionsTeamId($organisationId);

        if (!$user->can('SYSTEM_COLLECTOR_VIEW')) {
            abort(403, 'Vous n’avez pas l’autorisation de consulter les collecteurs.');
        }

        $collectors = OrganisationUser::with('user.roles')
            ->where('organisation_id', $organisationId)
            ->whereHas('user.roles', function ($q) {
                $q->where('name', 'SYSTEM_COLLECTEUR');
            })
            ->get()
            ->map(function ($ou) {
                return [
                    'id' => $ou->user->id,
                    'name' => $ou->user->name ?? $ou->user->email,
                    'email' => $ou->user->email,
                    'created_at' => $ou->created_at->format('Y-m-d H:i'),
                ];
            });

        return Inertia::render('Collectors/Index', [
            'collectors' => $collectors,
        ]);
    }

    public function create()
    {
        $organisationId = getPermissionsTeamId();

        if (!$organisationId) {
            abort(403, 'Aucune organisation active sélectionnée.');
        }

        setPermissionsTeamId($organisationId);

        if (!Auth::user()->can('SYSTEM_COLLECTOR_CREATE')) {
            abort(403, 'Vous n’êtes pas autorisé à ajouter des collecteurs.');
        }

        $role = Role::where('name', 'SYSTEM_COLLECTEUR')->firstOrFail();

        return Inertia::render('Collectors/Create', [
            'roles' => [
                [
                    'id' => $role->id,
                    'name' => $role->name,
                ],
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'roles' => ['required', 'array'],
        ]);

        $organisationId = getPermissionsTeamId();

        if (!$organisationId) {
            abort(403, 'Aucune organisation active sélectionnée.');
        }

        setPermissionsTeamId($organisationId);

        if (!Auth::user()->can('SYSTEM_COLLECTOR_CREATE')) {
            abort(403, 'Vous n’êtes pas autorisé à ajouter des collecteurs.');
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors([
                'email' => 'Aucun utilisateur ne correspond à cette adresse email.',
            ]);
        }

        OrganisationUser::firstOrCreate([
            'user_id' => $user->id,
            'organisation_id' => $organisationId,
        ]);

        $user->assignRole('SYSTEM_COLLECTEUR');

        return redirect()
            ->route('collectors.index')
            ->with('success', 'Le collecteur a été ajouté avec succès.');
    }

    public function destroy(User $user)
    {
        $organisationId = getPermissionsTeamId();

        if (!$organisationId) {
            return back()->with('error', 'Aucune organisation active sélectionnée.');
        }

        setPermissionsTeamId($organisationId);

        if (!Auth::user()->can('SYSTEM_COLLECTOR_DELETE')) {
            abort(403, 'Vous n’êtes pas autorisé à supprimer des collecteurs.');
        }

        $organisationUser = OrganisationUser::where('user_id', $user->id)
            ->where('organisation_id', $organisationId)
            ->first();

        if (!$organisationUser) {
            return back()->with('error', 'Ce collecteur n’existe pas dans l’organisation active.');
        }

        $user->removeRole('SYSTEM_COLLECTEUR');
        $organisationUser->delete();

        return back()->with('success', 'Le collecteur a été supprimé avec succès.');
    }
}
