<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CollectionPrix extends Model
{
    use HasFactory;

    protected $table = 'collections_prix';

    protected $fillable = [
        'commune_id',
        'arrondissement_id',
        'quartier_id',
        'materiau_id',
        'devise_id',
        'user_id',
        'categorie_id',
        'description_materiaux',
        'detail',
        'price',
        'status',
        'point_vente',
    ];

    protected $casts = [
        'status' => 'boolean',
        'price' => 'decimal:2',
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
}
