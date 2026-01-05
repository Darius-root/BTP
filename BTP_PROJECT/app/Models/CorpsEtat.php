<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CorpsEtat extends Model
{
    use HasFactory;

    protected $table = 'corps_etat';

    protected $fillable = ['user_id', 'code', 'intitule', 'ordre', 'sous_total'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function collectionsPrix(): HasMany
    {
        return $this->hasMany(CollectionPrix::class, 'categorie_id');
    }
}
