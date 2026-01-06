<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Materiau extends Model
{
    protected $table = 'materiaux';
    use HasFactory;

    protected $fillable = ['code', 'nom', 'unite_id'];

    public function unite(): BelongsTo
    {
        return $this->belongsTo(UniteMesure::class, 'unite_id');
    }

    public function collectionsPrix(): HasMany
    {
        return $this->hasMany(CollectionPrix::class);
    }
}
