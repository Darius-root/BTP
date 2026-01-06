<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Arrondissement extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'libelle', 'commune_id'];

    public function commune(): BelongsTo
    {
        return $this->belongsTo(Commune::class);
    }

    public function collectionsPrix(): HasMany
    {
        return $this->hasMany(CollectionPrix::class);
    }
}
