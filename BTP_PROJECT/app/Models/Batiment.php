<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Batiment extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'nom',
        'localisation',
        'description',
        'projet_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function projet(): BelongsTo
    {
        return $this->belongsTo(Projet::class);
    }

    public function devisEstimatif(): HasOne
    {
        return $this->hasOne(DevisEstimatif::class);
    }



  

}
