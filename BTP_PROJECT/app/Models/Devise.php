<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Devise extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'libelle', 'symbole'];

    public function collectionsPrix(): HasMany
    {
        return $this->hasMany(CollectionPrix::class);
    }
}
