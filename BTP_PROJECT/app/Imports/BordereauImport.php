<?php

namespace App\Imports;

use App\Models\Bordereau;
use App\Models\BordereauLigne;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log;

class BordereauImport implements ToCollection, WithHeadingRow
{
    protected int $annee;
    protected string $version;
    protected int $userId;

    public function __construct(int $annee, string $version, int $userId)
    {
        $this->annee = $annee;
        $this->version = $version;
        $this->userId = $userId;
    }

    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) {
            throw new \Exception("Le fichier ne contient aucune donnée.");
        }

        $lineCount = 0;
        $skippedCount = 0;

        foreach ($rows as $index => $row) {
            try {
                $code = str_replace(' ', '', trim($row['code'] ?? ''));
                if (!$code) {
                    Log::warning("Ligne {$index} ignorée : code vide");
                    $skippedCount++;
                    continue;
                }

                $bordereauLibelle = trim($row['libelle'] ?? '');
                if (!$bordereauLibelle) {
                    Log::warning("Ligne {$index} ignorée : libellé vide", ['code' => $code]);
                    $skippedCount++;
                    continue;
                }

                $designationsText = $row['designations'] ?? '';
                if (empty(trim($designationsText))) {
                    Log::warning("Ligne {$index} ignorée : désignations vides", ['code' => $code]);
                    $skippedCount++;
                    continue;
                }

                // Créer le bordereau pour cette ligne
                $bordereau = Bordereau::firstOrCreate(
                    [
                        'annee' => $this->annee,
                        'version' => $this->version,
                        'user_id' => $this->userId,
                        'libelle' => $bordereauLibelle
                    ]
                );

                // Séparer le texte en lignes pour les désignations
                $lines = preg_split('/\r\n|\r|\n/', $designationsText);
                $lines = array_map('trim', $lines);
                $lines = array_filter($lines);

                // La première ligne du bloc peut être répétitive, on la retire si identique au libellé
                $firstLine = array_shift($lines);
                if ($firstLine === $bordereauLibelle) {
                    $firstLine = null;
                }

                $designationLines = array_filter($lines, fn($line) => preg_match('/^[〉\)>]/u', $line));
                $designationLines = array_map(fn($line) => preg_replace('/^[〉\)>]\s*/u', '', $line), $designationLines);

                // Si aucune désignation trouvée, on considère tout le texte restant
                if (empty($designationLines) && !empty($lines)) {
                    $designationLines = $lines;
                }

                $bi = $this->parseNumeric($row['bi'] ?? 0);
                $bs = $this->parseNumeric($row['bs'] ?? 0);
                $uniteMesure = $row['unite_de_mesure'] ?? null;

                foreach ($designationLines as $designation) {
                    if (!$designation)
                        continue;

                    BordereauLigne::updateOrCreate(
                        [
                            'bordereau_id' => $bordereau->id,
                            'code' => $code,
                            'designation' => $designation,
                        ],
                        [
                            'specification_technique' => $uniteMesure,
                            'bi' => $bi,
                            'bs' => $bs,
                        ]
                    );

                    $lineCount++;
                }

            } catch (\Exception $e) {
                Log::error("Erreur lors de l'import de la ligne {$index}", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'row' => $row
                ]);
                throw new \Exception("Erreur ligne " . ($index + 2) . " : " . $e->getMessage());
            }
        }

        Log::info("Import terminé", [
            'lignes_importees' => $lineCount,
            'lignes_ignorees' => $skippedCount
        ]);
    }

    protected function parseNumeric($value)
    {
        if (is_numeric($value))
            return floatval($value);
        $cleaned = str_replace([' ', ','], ['', '.'], trim($value));
        return is_numeric($cleaned) ? floatval($cleaned) : 0;
    }

    public function headingRow(): int
    {
        return 1;
    }
}
