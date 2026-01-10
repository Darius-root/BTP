<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DevisEstimatif extends Model
{
    protected $table = 'devis_estimatif';

    protected $fillable = [
        'batiment_id',
        'intitule',
        'code',
        'statut',
        'is_template',
        'template_id',
        'created_by',
    ];

    protected $casts = [
        'is_template' => 'boolean',
    ];

    // =====================
    // RELATIONS
    // =====================

    public function batiment()
    {
        return $this->belongsTo(Batiment::class);
    }

    public function template()
    {
        return $this->belongsTo(TemplateDevis::class, 'template_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function composants()
    {
        return $this->hasMany(ComposantNiveau::class);
    }

    // =====================
    // MÉTHODES MÉTIER
    // =====================

    public function total()
    {
        return $this->composants()->sum('montant');
    }

    public function totalParNiveau()
    {
        return $this->composants()
            ->selectRaw('niveau_id, SUM(montant) as total')
            ->groupBy('niveau_id')
            ->with('niveau')
            ->get();
    }
}
