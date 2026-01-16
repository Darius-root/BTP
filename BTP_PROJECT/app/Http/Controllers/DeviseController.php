<?php

namespace App\Http\Controllers;

use App\Models\Devise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
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

    public function show(Devise $devise)
    {
        if (!Auth::user()->can('SYSTEM_DEVISE_VIEW')) {
            return back()->with(
                'error',
                "Vous n’êtes pas autorisé à consulter cette devise."
            );
        }

        return Inertia::render('Devises/Show', [
            'devise' => $devise,
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
            return back()->with('error', "Vous n’avez pas l’autorisation de créer une devise.");
        }

        $validated = $request->validate([
            'libelle' => 'required|string|max:255|unique:devises,libelle',
            'symbole' => 'nullable|string|max:10',
        ]);

        $libelleClean = strtoupper(Str::ascii($validated['libelle']));
        $letters = substr(preg_replace('/[^A-Z]/', '', $libelleClean), 0, 3);
        $letters = str_pad($letters, 3, 'X'); // si moins de 3 lettres

        $prefix = "DEV-{$letters}-";

        do {
            $randomNumber = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
            $code = $prefix . $randomNumber;
        } while (Devise::where('code', $code)->exists());

        Devise::create([
            'code' => $code,
            'libelle' => $validated['libelle'],
            'symbole' => $validated['symbole'],
        ]);

        return redirect()
            ->route('devises.index')
            ->with('success', "La devise a été créée avec succès.");
    }

    public function update(Request $request, Devise $devise)
    {
        if (!Auth::user()->can('SYSTEM_DEVISE_EDIT')) {
            return back()->with('error', "Vous n’avez pas l’autorisation de modifier cette devise.");
        }

        $validated = $request->validate([
            'libelle' => 'required|string|max:255|unique:devises,libelle,' . $devise->id,
            'symbole' => 'nullable|string|max:10',
        ]);

        $libelleClean = strtoupper(Str::ascii($validated['libelle']));
        $letters = substr(preg_replace('/[^A-Z]/', '', $libelleClean), 0, 3);
        $letters = str_pad($letters, 3, 'X');

        $prefix = "DEV-{$letters}-";

        do {
            $randomNumber = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
            $newCode = $prefix . $randomNumber;
        } while (
            Devise::where('code', $newCode)
                ->where('id', '!=', $devise->id)
                ->exists()
        );

        $devise->update([
            'code' => $newCode,
            'libelle' => $validated['libelle'],
            'symbole' => $validated['symbole'],
        ]);

        return redirect()
            ->route('devises.index')
            ->with('success', "La devise a été mise à jour avec succès et code recalculé.");
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

