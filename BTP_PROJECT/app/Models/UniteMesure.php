<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UniteMesure extends Model
{
    protected $table = 'unites_mesure';
    use HasFactory;

    protected $fillable = ['code', 'libelle'];

    public function materiaux(): HasMany
    {
        return $this->hasMany(Materiau::class, 'unite_id');
    }
}
