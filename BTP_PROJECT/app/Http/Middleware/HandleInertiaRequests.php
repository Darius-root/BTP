<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Spatie\Permission\PermissionRegistrar;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */

public function share(Request $request): array
{
    // Important : définir la team AVANT de lire les permissions
    if ($request->session()->has('active_organisation_id')) {
        app(PermissionRegistrar::class)
            ->setPermissionsTeamId(
                $request->session()->get('active_organisation_id')
            );
    }

    $user = $request->user();

    return [
        ...parent::share($request),

        'auth' => [
            'user' => $user,
            'permissions' => $user
                ? $user->getAllPermissions()->pluck('name')
                : [],
        ],

        'session' => [
            'active_organisation_id'   => $request->session()->get('active_organisation_id'),
            'active_organisation_name' => $request->session()->get('active_organisation_name'),
        ],

        'flash' => [
            'success' => fn() => $request->session()->get('success'),
            'error'   => fn() => $request->session()->get('error'),
        ],
    ];
}

}
