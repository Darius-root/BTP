<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Commune extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'libelle'];

    public function arrondissements(): HasMany
    {
        return $this->hasMany(Arrondissement::class);
    }

    public function collectionsPrix(): HasMany
    {
        return $this->hasMany(CollectionPrix::class);
    }
}
