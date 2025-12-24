<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bordereau extends Model
{
    use HasFactory;
    protected $table = 'bordereaux';

    protected $fillable = [
        'libelle',
        'user_id',
        'annee',
        'version',
    ];

    protected $casts = [
        'annee' => 'integer',
    ];

    /**
     * Relation avec l'utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec les lignes du bordereau
     */
    public function lignes(): HasMany
    {
        return $this->hasMany(BordereauLigne::class);
    }
}
