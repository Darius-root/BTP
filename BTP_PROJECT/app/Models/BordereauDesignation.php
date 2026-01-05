<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BordereauDesignation  extends Model
{
        protected $appends = ['caracteristiques_array'];

    protected $fillable = [
        'code',
        'designation',
        'caracteristiques',
        'unite_mesure',
        'bi',
        'bs',
        'bordereau_id',
    ];

    protected $casts = [
        'bi' => 'decimal:2',
        'bs' => 'decimal:2',
    ];

    public function bordereau(): BelongsTo
    {
        return $this->belongsTo(Bordereau::class);
    }

    /**
     * Récupère les caractéristiques sous forme de tableau
     */
    public function getCaracteristiquesArrayAttribute(): array
    {
        preg_match_all('/〉\s*(.+?)(?=〉|$)/s', $this->caracteristiques, $matches);
        return array_map('trim', $matches[1] ?? []);
    }

     public function getCaracteristiquesFormattedAttribute(): array
    {
        // Si caractéristiques est déjà un tableau
        if (is_array($this->caracteristiques)) {
            return $this->caracteristiques;
        }

        // Si caractéristiques est une chaîne vide
        if (empty($this->caracteristiques)) {
            return [];
        }

        // Pour le format avec "〉" comme séparateur
        $caracteristiques = explode('〉', $this->caracteristiques);

        // Nettoyer et filtrer les éléments
        return array_map(function($item) {
            return trim($item);
        }, array_filter($caracteristiques, function($item) {
            return !empty(trim($item));
        }));
    }
}
