<?php

namespace App\Imports;

use App\Models\Bordereau;
use App\Models\BordereauDesignation;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\IValueBinder;

class BordereauImport extends DefaultValueBinder
    implements IValueBinder, ToModel, WithHeadingRow, SkipsEmptyRows
{
    private string $nomBordereau;
    private string $annee;
    private string $version;
    private int $userId;

    private ?Bordereau $bordereau = null;

    public function __construct(
        string $nomBordereau,
        string $annee,
        string $version,
        int $userId
    ) {
        $this->nomBordereau = $nomBordereau;
        $this->annee = $annee;
        $this->version = $version;
        $this->userId = $userId;
    }

    /**
     * Traitement d'une ligne Excel
     */
    public function model(array $row)
    {
        // --- Sécurisation minimale des données ---
        $code = $this->cleanString($row['code'] ?? null);
        $designation = $this->cleanString($row['designation'] ?? null);

        // Ignorer les lignes vides / parasites
        if ($code === '' || $designation === '') {
            return null;
        }

        // --- Création du bordereau (1 seule fois par fichier) ---
        if (!$this->bordereau) {
            $this->bordereau = Bordereau::firstOrCreate(
                [
                    'nom_bordereau' => $this->nomBordereau,
                    'annee' => $this->annee,
                    'version' => $this->version,
                ],
                [
                    'user_id' => $this->userId,
                ]
            );
        }

        // --- Protection contre doublon (bordereau_id + code) ---
        if (
            BordereauDesignation::where('bordereau_id', $this->bordereau->id)
                ->where('code', $code)
                ->exists()
        ) {
            return null;
        }

        return new BordereauDesignation([
            'bordereau_id'   => $this->bordereau->id,
            'code'           => $code,
            'designation'    => $designation,
            'caracteristiques' => $this->normalizeCaracteristiques(
                $row['caracteristiques'] ?? ''
            ),
            'unite_mesure'   => $this->cleanString(
                $row['unite_de_mesure'] ?? ''
            ),
            'bi'             => $this->cleanNumeric($row['bi'] ?? null),
            'bs'             => $this->cleanNumeric($row['bs'] ?? null),
        ]);
    }

    /**
     * Forcer la colonne CODE en string (Excel + gros numéros)
     */
    public function bindValue(Cell $cell, $value)
    {
        if ($cell->getColumn() === 'A') {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);
            return true;
        }

        return parent::bindValue($cell, $value);
    }

    /* =====================================================
       Helpers
       ===================================================== */

    private function cleanString($value): string
    {
        if ($value === null) {
            return '';
        }

        $value = trim((string) $value);
        return preg_replace('/[\x00-\x1F\x7F]/u', '', $value);
    }

    private function cleanNumeric($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        // 21 600 000 → 21600000
        $value = preg_replace('/[^0-9.,\-]/', '', (string) $value);
        $value = str_replace(',', '.', $value);

        $parts = explode('.', $value);
        if (count($parts) > 2) {
            $value = $parts[0] . '.' . implode('', array_slice($parts, 1));
        }

        return is_numeric($value) ? (float) $value : null;
    }

    private function normalizeCaracteristiques(string $texte): string
    {
        if ($texte === '') {
            return '';
        }

        $texte = $this->cleanString($texte);

        // Déjà normalisé
        if (str_contains($texte, '〉')) {
            return $texte;
        }

        $lines = array_filter(array_map('trim', explode("\n", $texte)));

        if (count($lines) <= 1) {
            return $texte;
        }

        $titre = array_shift($lines);
        $result = $titre . "\n";

        foreach ($lines as $line) {
            $result .= '〉 ' . ltrim($line, "-•* ") . "\n";
        }

        return trim($result);
    }
}
