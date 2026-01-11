<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComposantNiveau extends Model
{
    use HasFactory;

    protected $table = 'composants_niveau';

    protected $fillable = [
        'niveau_id',
        'devis_estimatif_id',
        'code',
        'piece',
        'unite_id',
        'qte',
        'prix_unitaire',
        'montant',
    ];

    protected $casts = [
        'qte' => 'decimal:2',
        'prix_unitaire' => 'decimal:2',
        'montant' => 'decimal:2',
    ];

    // =====================
    // RELATIONS
    // =====================

    public function devisEstimatif()
    {
        return $this->belongsTo(DevisEstimatif::class, 'devis_estimatif_id');
    }

    public function niveau()
    {
        return $this->belongsTo(NiveauBatiment::class, 'niveau_id');
    }

    public function unite()
    {
        return $this->belongsTo(UniteMesure::class, 'unite_id');
    }
}
