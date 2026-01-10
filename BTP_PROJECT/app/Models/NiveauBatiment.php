<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NiveauBatiment extends Model
{
    use HasFactory;

    protected $table = 'niveaux_batiment';

    protected $fillable = [
        'user_id',
        'code',
        'nom',
        'description',
    ];



    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}