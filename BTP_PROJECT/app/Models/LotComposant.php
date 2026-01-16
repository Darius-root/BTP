<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LotComposant extends Model
{
    protected $table = 'lots_composants';

    protected $fillable = [
        'lot_id',
        'code',
        'designation',
        'unite_id',
        'quantite',
        'prix_unitaire',
        'montant',
    ];

    protected static function booted()
    {
        static::saving(function ($composant) {
            $composant->montant = $composant->quantite * $composant->prix_unitaire;
        });

        static::saved(function ($composant) {
            $composant->lot->recalculerSousTotal();
        });
    }

    public function lot()
    {
        return $this->belongsTo(DevisLot::class, 'lot_id');
    }

    public function unite()
    {
        return $this->belongsTo(UniteMesure::class);
    }
}
