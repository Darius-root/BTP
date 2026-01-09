<?php

namespace App\Http\Controllers;

use App\Models\CorpsEtat;
use App\Services\OrganisationContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Throwable;

class CorpsEtatController extends Controller
{
    /**
     * Afficher la liste des corps d'état
     */
    public function index()
    {
        try {
            $activeOrg = getPermissionsTeamId();
            if (!OrganisationContext::hasPermission(Auth::user(), $activeOrg, 'SYSTEM_CORPS_ETAT_VIEW')) {
                return back()->with('error', "Vous n'avez pas la permission de voir les corps d'état.");
            }

            $corpsEtats = CorpsEtat::where('user_id', Auth::id())
                ->orderBy('ordre')
                ->get();

            return Inertia::render('CorpsEtat/Index', [
                'corpsEtats' => $corpsEtats,
            ]);

        } catch (Throwable $e) {
            Log::error('Erreur index corps état: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors du chargement des corps d\'état.');
        }
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        try {
            $activeOrg = getPermissionsTeamId();
            if (!OrganisationContext::hasPermission(Auth::user(), $activeOrg, 'SYSTEM_CORPS_ETAT_CREATE')) {
                return back()->with('error', "Vous n'avez pas la permission de créer un corps d'état.");
            }

            return Inertia::render('CorpsEtat/Create');

        } catch (Throwable $e) {
            Log::error('Erreur create corps état: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de l\'accès au formulaire.');
        }
    }

    /**
     * Enregistrer un nouveau corps d'état
     */
    public function store(Request $request)
    {
        try {
            $activeOrg = getPermissionsTeamId();
            if (!OrganisationContext::hasPermission(Auth::user(), $activeOrg, 'SYSTEM_CORPS_ETAT_CREATE')) {
                return back()->with('error', "Vous n'avez pas la permission de créer un corps d'état.");
            }

            $validated = $request->validate([
                'code' => 'required|string|max:10|unique:corps_etats',
                'intitule' => 'required|string|max:255|unique:corps_etats',
                'ordre' => 'required|integer',
                'sous_total' => 'nullable|numeric|min:0',
            ]);

            DB::beginTransaction();

            CorpsEtat::create(array_merge($validated, [
                'user_id' => Auth::id(),
            ]));

            DB::commit();

            return redirect()->route('corps-etat.index')
                ->with('success', 'Corps d\'état créé avec succès.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()
                ->withInput()
                ->withErrors($e->errors());

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Erreur store corps état: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la création du corps d\'état.');
        }
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(CorpsEtat $corpsEtat)
    {
        try {
            $activeOrg = getPermissionsTeamId();
            if (!OrganisationContext::hasPermission(Auth::user(), $activeOrg, 'SYSTEM_CORPS_ETAT_EDIT')) {
                return back()->with('error', "Vous n'avez pas la permission de modifier ce corps d'état.");
            }

            // Vérifier que le corps d'état appartient à l'utilisateur
            if ($corpsEtat->user_id !== Auth::id()) {
                return back()->with('error', 'Accès interdit à ce corps d\'état.');
            }

            return Inertia::render('CorpsEtat/Edit', [
                'corpsEtat' => $corpsEtat,
            ]);

        } catch (Throwable $e) {
            Log::error('Erreur edit corps état: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de l\'accès au corps d\'état.');
        }
    }

    /**
     * Mettre à jour un corps d'état
     */
    public function update(Request $request, CorpsEtat $corpsEtat)
    {
        try {
            $activeOrg = getPermissionsTeamId();
            if (!OrganisationContext::hasPermission(Auth::user(), $activeOrg, 'SYSTEM_CORPS_ETAT_EDIT')) {
                return back()->with('error', "Vous n'avez pas la permission de modifier ce corps d'état.");
            }

            // Vérifier que le corps d'état appartient à l'utilisateur
            if ($corpsEtat->user_id !== Auth::id()) {
                return back()->with('error', 'Accès interdit à ce corps d\'état.');
            }

            $validated = $request->validate([
                'code' => 'required|string|max:10|unique:corps_etats,code,' . $corpsEtat->id,
                'intitule' => 'required|string|max:255|unique:corps_etats,intitule,' . $corpsEtat->id,
                'ordre' => 'required|integer',
                'sous_total' => 'nullable|numeric|min:0',
            ]);

            $corpsEtat->update($validated);

            return redirect()->route('corps-etat.index')
                ->with('success', 'Corps d\'état mis à jour avec succès.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()
                ->withInput()
                ->withErrors($e->errors());

        } catch (Throwable $e) {
            Log::error('Erreur update corps état: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la mise à jour du corps d\'état.');
        }
    }

    /**
     * Supprimer un corps d'état
     */
    public function destroy(CorpsEtat $corpsEtat)
    {
        try {
            $activeOrg = getPermissionsTeamId();
            if (!OrganisationContext::hasPermission(Auth::user(), $activeOrg, 'SYSTEM_CORPS_ETAT_DELETE')) {
                return back()->with('error', "Vous n'avez pas la permission de supprimer ce corps d'état.");
            }

            // Vérifier que le corps d'état appartient à l'utilisateur
            if ($corpsEtat->user_id !== Auth::id()) {
                return back()->with('error', 'Accès interdit à ce corps d\'état.');
            }

            $corpsEtat->delete();

            return redirect()->route('corps-etat.index')
                ->with('success', 'Corps d\'état supprimé avec succès.');

        } catch (Throwable $e) {
            Log::error('Erreur destroy corps état: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de la suppression du corps d\'état.');
        }
    }
}
