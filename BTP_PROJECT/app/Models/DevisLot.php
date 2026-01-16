<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DevisLot extends Model
{
    protected $table = 'devis_lots';

    protected $fillable = [
        'corps_etat_id',
        'devis_id',
        'code',
        'intitule',
        'ordre',
        'sous_total',
    ];

    public function devis()
    {
        return $this->belongsTo(DevisEstimatifQuantitatif::class, 'devis_id');
    }

    public function corpsEtat()
    {
        return $this->belongsTo(CorpsEtat::class);
    }

    public function composants()
    {
        return $this->hasMany(LotComposant::class, 'lot_id');
    }

    public function recalculerSousTotal()
    {
        $this->sous_total = $this->composants()->sum('montant');
        $this->save();
    }
}
