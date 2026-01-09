<?php

namespace App\Http\Controllers;

use App\Models\Devise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DeviseController extends Controller
{
    public function index()
    {
        if (!Auth::user()->can('SYSTEM_DEVISE_VIEW')) {
            return back()->with(
                'error',
                "Vous n’êtes pas autorisé à consulter la liste des devises."
            );
        }

        return Inertia::render('Devises/Index', [
            'devises' => Devise::orderBy('code')->get(),
        ]);
    }

    public function create()
    {
        if (!Auth::user()->can('SYSTEM_DEVISE_CREATE')) {
            return back()->with(
                'error',
                "Vous n’avez pas l’autorisation de créer une devise."
            );
        }

        return Inertia::render('Devises/Create');
    }

    public function store(Request $request)
    {
        if (!Auth::user()->can('SYSTEM_DEVISE_CREATE')) {
            return back()->with(
                'error',
                "Vous n’avez pas l’autorisation de créer une devise."
            );
        }

        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:devises',
            'libelle' => 'required|string|max:255|unique:devises',
            'symbole' => 'nullable|string|max:10',
        ]);

        Devise::create($validated);

        return redirect()
            ->route('devises.index')
            ->with('success', "La devise a été créée avec succès.");
    }

    public function edit(Devise $devise)
    {
        if (!Auth::user()->can('SYSTEM_DEVISE_EDIT')) {
            return back()->with(
                'error',
                "Vous n’avez pas l’autorisation de modifier cette devise."
            );
        }

        return Inertia::render('Devises/Edit', [
            'devise' => $devise,
        ]);
    }

    public function update(Request $request, Devise $devise)
    {
        if (!Auth::user()->can('SYSTEM_DEVISE_EDIT')) {
            return back()->with(
                'error',
                "Vous n’avez pas l’autorisation de modifier cette devise."
            );
        }

        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:devises,code,' . $devise->id,
            'libelle' => 'required|string|max:255|unique:devises,libelle,' . $devise->id,
            'symbole' => 'nullable|string|max:10',
        ]);

        $devise->update($validated);

        return redirect()
            ->route('devises.index')
            ->with('success', "La devise a été mise à jour avec succès.");
    }

    public function destroy(Devise $devise)
    {
        if (!Auth::user()->can('SYSTEM_DEVISE_DELETE')) {
            return back()->with(
                'error',
                "Vous n’avez pas l’autorisation de supprimer cette devise."
            );
        }

        $devise->delete();

        return redirect()
            ->route('devises.index')
            ->with('success', "La devise a été supprimée avec succès.");
    }
}
