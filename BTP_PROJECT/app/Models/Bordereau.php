<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bordereau extends Model
{
     protected $table = 'bordereaux'; 
    protected $fillable = [
        'nom_bordereau',
        'annee',
        'version',
        'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function designations(): HasMany
    {
        return $this->hasMany(BordereauDesignation::class);
    }
}
