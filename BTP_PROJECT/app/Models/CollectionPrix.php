<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class CollectionPrix extends Model
{
    use HasFactory;

    protected $table = 'collections_prix';

    protected $fillable = [
        'commune_id',
        'arrondissement_id',
        'quartier',
        'materiau_id',
        'unite_id',
        'devise_id',
        'user_id',
        'categorie_id',
        'description_materiaux',
        'detail',
        'price',
        'is_validated',
        'validated_by',
        'validated_at',
        'point_vente',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_validated' => 'boolean',
        'validated_at' => 'datetime',
    ];



    public function commune(): BelongsTo
    {
        return $this->belongsTo(Commune::class);
    }

    public function arrondissement(): BelongsTo
    {
        return $this->belongsTo(Arrondissement::class);
    }

    public function materiau(): BelongsTo
    {
        return $this->belongsTo(Materiau::class);
    }

    public function unite(): BelongsTo
    {
        return $this->belongsTo(UniteMesure::class);
    }

    public function devise(): BelongsTo
    {
        return $this->belongsTo(Devise::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function categorie(): BelongsTo
    {
        return $this->belongsTo(CorpsEtat::class, 'categorie_id');
    }

    /**
     * Admin ayant validé la collecte
     */
    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }



    /**
     * Collectes validées (visibles globalement)
     */
    public function scopeValidated(Builder $query): Builder
    {
        return $query->where('is_validated', true);
    }

    /**
     * Collectes en attente de validation
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('is_validated', false);
    }

    /**
     * Collectes d’un utilisateur donné
     */
    public function scopeOwnedBy(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }



    public function isValidated(): bool
    {
        return $this->is_validated === true;
    }


}
