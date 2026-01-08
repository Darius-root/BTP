<?php

namespace App\Http\Controllers;

use App\Models\Devise;
use App\Services\OrganisationContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Throwable;

class DeviseController extends Controller
{
    /**
     * Afficher la liste des devises
     */
    public function index()
    {
        try {
            $activeOrg = getPermissionsTeamId();
            if (!OrganisationContext::hasPermission(Auth::user(), $activeOrg, 'SYSTEM_DEVISE_VIEW')) {
                return back()->with('error', "Vous n'avez pas la permission de voir les devises.");
            }

            $devises = Devise::orderBy('code')->get();

            return Inertia::render('Devises/Index', [
                'devises' => $devises,
            ]);

        } catch (Throwable $e) {
            Log::error('Erreur index devises: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors du chargement des devises.');
        }
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        try {
            $activeOrg = getPermissionsTeamId();
            if (!OrganisationContext::hasPermission(Auth::user(), $activeOrg, 'SYSTEM_DEVISE_CREATE')) {
                return back()->with('error', "Vous n'avez pas la permission de créer une devise.");
            }

            return Inertia::render('Devises/Create');

        } catch (Throwable $e) {
            Log::error('Erreur create devise: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de l\'accès au formulaire.');
        }
    }

    /**
     * Enregistrer une nouvelle devise
     */
    public function store(Request $request)
    {
        try {
            $activeOrg = getPermissionsTeamId();
            if (!OrganisationContext::hasPermission(Auth::user(), $activeOrg, 'SYSTEM_DEVISE_CREATE')) {
                return back()->with('error', "Vous n'avez pas la permission de créer une devise.");
            }

            $validated = $request->validate([
                'code' => 'required|string|max:10|unique:devises',
                'libelle' => 'required|string|max:255|unique:devises',
                'symbole' => 'nullable|string|max:10',
            ]);

            DB::beginTransaction();

            Devise::create($validated);

            DB::commit();

            return redirect()->route('devises.index')
                ->with('success', 'Devise créée avec succès.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()
                ->withInput()
                ->withErrors($e->errors());

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Erreur store devise: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la création de la devise.');
        }
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(Devise $devise)
    {
        try {
            $activeOrg = getPermissionsTeamId();
            if (!OrganisationContext::hasPermission(Auth::user(), $activeOrg, 'SYSTEM_DEVISE_EDIT')) {
                return back()->with('error', "Vous n'avez pas la permission de modifier cette devise.");
            }

            return Inertia::render('Devises/Edit', [
                'devise' => $devise,
            ]);

        } catch (Throwable $e) {
            Log::error('Erreur edit devise: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de l\'accès à la devise.');
        }
    }

    /**
     * Mettre à jour une devise
     */
    public function update(Request $request, Devise $devise)
    {
        try {
            $activeOrg = getPermissionsTeamId();
            if (!OrganisationContext::hasPermission(Auth::user(), $activeOrg, 'SYSTEM_DEVISE_EDIT')) {
                return back()->with('error', "Vous n'avez pas la permission de modifier cette devise.");
            }

            $validated = $request->validate([
                'code' => 'required|string|max:10|unique:devises,code,' . $devise->id,
                'libelle' => 'required|string|max:255|unique:devises,libelle,' . $devise->id,
                'symbole' => 'nullable|string|max:10',
            ]);

            $devise->update($validated);

            return redirect()->route('devises.index')
                ->with('success', 'Devise mise à jour avec succès.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()
                ->withInput()
                ->withErrors($e->errors());

        } catch (Throwable $e) {
            Log::error('Erreur update devise: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la mise à jour de la devise.');
        }
    }

    /**
     * Supprimer une devise
     */
    public function destroy(Devise $devise)
    {
        try {
            $activeOrg = getPermissionsTeamId();
            if (!OrganisationContext::hasPermission(Auth::user(), $activeOrg, 'SYSTEM_DEVISE_DELETE')) {
                return back()->with('error', "Vous n'avez pas la permission de supprimer cette devise.");
            }

            // Vérifier si la devise est utilisée dans des projets
            if ($devise->projets()->exists()) {
                return back()->with('error', 'Impossible de supprimer cette devise car elle est utilisée dans des projets.');
            }

            $devise->delete();

            return redirect()->route('devises.index')
                ->with('success', 'Devise supprimée avec succès.');

        } catch (Throwable $e) {
            Log::error('Erreur destroy devise: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de la suppression de la devise.');
        }
    }
}
