<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Projet extends Model
{
    use HasFactory;

    protected $fillable = [
        'devise_id',
        'tva',
        'code_projet',
        'nom',
        'localisation',
        'resume',
        'budget_previsionnel',
        'type_projet',
        'client_id',
        'organisation_id',
    ];

    protected $casts = [
        'budget_previsionnel' => 'decimal:2',
        'tva' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function devise(): BelongsTo
    {
        return $this->belongsTo(Devise::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function batiments(): HasMany
    {
        return $this->hasMany(Batiment::class);
    }
}
