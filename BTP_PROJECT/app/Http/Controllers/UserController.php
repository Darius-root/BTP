<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\OrganisationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $columns = $request->input('columns', []);

        // Colonnes autorisées pour la recherche
        $allowed = ['id', 'name', 'email', 'created_at'];

        $users = User::query()
            ->when($search && count($columns), function ($q) use ($search, $columns, $allowed) {
                $q->where(function ($sub) use ($search, $columns, $allowed) {
                    foreach ($columns as $column) {
                        if (!in_array($column, $allowed)) {
                            continue;
                        }

                        if ($column === 'created_at') {
                            $sub->orWhereDate('created_at', $search);
                        } else {
                            $sub->orWhere($column, 'like', "%{$search}%");
                        }
                    }
                });
            })
            ->paginate(5)
            ->withQueryString();

        return inertia('Users/Index', [
            'users' => $users,
        ]);
    }



  public function show(User $user, OrganisationService $organisationService)
{

    $organisationsWithRoles = [];

    // Sauvegarde de la team active actuelle (par défaut)
    $currentTeamId = getPermissionsTeamId();
    foreach ($user->organisations as $organisation) {
        // Définit la team courante pour Spatie
        setPermissionsTeamId($organisation->id);

        // Réinitialise les relations pour forcer le rechargement
        $user->unsetRelation('roles')->unsetRelation('permissions');

        $organisationsWithRoles[] = [
            'team' => $organisation,
            'roles' => $user->getRoleNames(), // Rôles pour cette team
        ];
    }

    $user->unsetRelation('roles')->unsetRelation('permissions');

    // Restaure la team initiale
    setPermissionsTeamId($currentTeamId);
   
    return Inertia::render('Users/Show', [
        'user' => $user,
        'teamsWithRoles' => $organisationsWithRoles,
    ]);
}

}
