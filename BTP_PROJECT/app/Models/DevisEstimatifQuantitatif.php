<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DevisEstimatifQuantitatif extends Model
{
    protected $table = 'devis_estimatif_quantitatif';

    protected $fillable = [
        'batiment_id',
        'intitule',
        'code',
        'statut',
        'is_template',
        'template_id',
        'created_by',
    ];

    public function batiment()
    {
        return $this->belongsTo(Batiment::class);
    }

    public function lots()
    {
        return $this->hasMany(DevisLot::class, 'devis_id');
    }

    public function corpsEtats()
    {
        return $this->hasManyThrough(
            CorpsEtat::class,
            DevisLot::class,
            'devis_id',
            'id',
            'id',
            'corps_etat_id'
        )->distinct();
    }

    public function total()
    {
        return $this->lots()->sum('sous_total');
    }
}
