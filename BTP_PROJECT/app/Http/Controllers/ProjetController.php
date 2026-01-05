<?php

namespace App\Http\Controllers;

use App\Models\Projet;
use App\Models\Client;
use App\Models\Organisation;
use App\Models\Devise;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class ProjetController extends Controller
{
    // Constante pour l'organisation active
    private const ACTIVE_ORGANISATION_ID = 2;

    public function index(): Response
    {
        $projets = Projet::with(['client', 'organisation', 'devise'])
            ->latest()
            ->paginate(10);

        return Inertia::render('Organisations/Projets/Index', [
            'projets' => $projets
        ]);
    }

    public function create(): Response
    {
        // Clients uniquement de l'organisation active
        $clients = Client::where('organisation_id', self::ACTIVE_ORGANISATION_ID)->get();

        // L'organisation active seule
        $organisations = Organisation::where('id', self::ACTIVE_ORGANISATION_ID)->get();

        $devises = Devise::all();

        return Inertia::render('Organisations/Projets/Create', [
            'clients' => $clients,
            'organisations' => $organisations,
            'devises' => $devises
        ]);
    }

    /**
     * Générer un code projet unique
     * Format: [lettres Org][lettres Projet][Code numérique]
     * Exemple: PRGHR001, BTPGHR002, etc.
     */
    /**
     * Générer un code projet
     * Format : [ORG][PRJ][NNN]
     */
    private function generateCodeProjet(string $nomProjet, int $organisationId): string
    {
        $organisation = Organisation::findOrFail($organisationId);

        // 3 lettres de l'organisation
        $orgCode = $this->extractThreeLetters($organisation->name);

        // 3 lettres du projet
        $projectCode = $this->extractThreeLetters($nomProjet);

        $prefix = $orgCode . $projectCode;

        // Récupérer le dernier code utilisé pour ce préfixe
        $lastProjet = Projet::where('code_projet', 'LIKE', $prefix . '%')
            ->orderByRaw('CAST(SUBSTRING(code_projet, 7) AS UNSIGNED) DESC')
            ->first();

        $nextNumber = 1;

        if ($lastProjet) {
            $lastNumber = (int) Str::substr($lastProjet->code_projet, 6);
            $nextNumber++;
        }

        return $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }


    /**
     * Extraire lettres significatives d'un texte
     * - Si 1 mot : prendre les 3 premières lettres
     * - Si 2+ mots : prendre la première lettre de chaque mot (SANS complétion avec X)
     */
    private function extractThreeLetters(string $text): string
    {
        $text = strtoupper(trim($text));
        $text = preg_replace('/[^A-Z\s]/', '', $text);

        $words = array_values(array_filter(explode(' ', $text)));

        if (empty($words)) {
            return 'XXX';
        }

        // Un seul mot → 3 premières lettres
        if (count($words) === 1) {
            return Str::padRight(Str::substr($words[0], 0, 3), 3, 'X');
        }

        // Plusieurs mots → initiales (complétées avec X si < 3)
        $letters = '';
        foreach ($words as $word) {
            if (strlen($letters) < 3) {
                $letters .= Str::substr($word, 0, 1);
            }
        }

        return Str::padRight($letters, 3, 'X');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'devise_id' => 'nullable|exists:devises,id',
            'tva' => 'required|integer|min:0|max:100',
            'nom' => 'required|string|unique:projets,nom|max:255',
            'localisation' => 'nullable|string|max:255',
            'resume' => 'nullable|string',
            'budget_previsionnel' => 'nullable|numeric|min:0',
            'type_projet' => 'nullable|string|max:255',
            'client_id' => 'required|exists:clients,id',
        ]);

        // Force l'organisation active
        $validated['organisation_id'] = self::ACTIVE_ORGANISATION_ID;

        // Générer automatiquement le code projet
        $validated['code_projet'] = $this->generateCodeProjet(
            $validated['nom'],
            self::ACTIVE_ORGANISATION_ID
        );

        Projet::create($validated);

        return redirect()->route('projets.index')
            ->with('success', 'Projet créé avec succès.');
    }

    public function show(Projet $projet): Response
    {
        $projet->load(['client', 'organisation', 'devise', 'batiments']);

        return Inertia::render('Organisations/Projets/Show', [
            'projet' => $projet
        ]);
    }

    public function edit(Projet $projet): Response
    {
        // Clients uniquement de l'organisation active
        $clients = Client::where('organisation_id', self::ACTIVE_ORGANISATION_ID)->get();

        // Organisation active seule
        $organisations = Organisation::where('id', self::ACTIVE_ORGANISATION_ID)->get();

        $devises = Devise::all();

        return Inertia::render('Organisations/Projets/Edit', [
            'projet' => $projet,
            'clients' => $clients,
            'organisations' => $organisations,
            'devises' => $devises
        ]);
    }

    public function update(Request $request, Projet $projet): RedirectResponse
    {
        $validated = $request->validate([
            'devise_id' => 'nullable|exists:devises,id',
            'tva' => 'required|integer|min:0|max:100',
            'code_projet' => 'required|string|max:255|unique:projets,code_projet,' . $projet->id,
            'nom' => 'required|string|max:255|unique:projets,nom,' . $projet->id,
            'localisation' => 'nullable|string|max:255',
            'resume' => 'nullable|string',
            'budget_previsionnel' => 'nullable|numeric|min:0',
            'type_projet' => 'nullable|string|max:255',
            'client_id' => 'required|exists:clients,id',
        ]);

        // Force l'organisation active
        $validated['organisation_id'] = self::ACTIVE_ORGANISATION_ID;

        $projet->update($validated);

        return redirect()->route('projets.index')
            ->with('success', 'Projet mis à jour avec succès.');
    }

    public function destroy(Projet $projet): RedirectResponse
    {
        $projet->delete();

        return redirect()->route('projets.index')
            ->with('success', 'Projet supprimé avec succès.');
    }
}
