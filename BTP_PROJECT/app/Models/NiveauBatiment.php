<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NiveauBatiment extends Model
{
    use HasFactory;

    protected $table = 'niveaux_batiment';

    protected $fillable = [
        
        'code',
        'nom',
        'description',
    ];



   


    public function composants()
    {
        return $this->hasMany(ComposantNiveau::class, 'niveau_id');
    }
}
