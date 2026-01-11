<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Organisation extends Model
{
    use HasFactory;

    protected $fillable = ['nom', 'is_system', 'raison_sociale', 'logo', 'adresse', 'pays', 'devise', 'user_id'];


    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Une organisation a plusieurs membres
    public function organisationUsers()
    {
        return $this->hasMany(OrganisationUser::class);
    }


    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function projets(): HasMany
    {
        return $this->hasMany(Projet::class);
    }




   
    public function batiments(): HasManyThrough
    {
        return $this->hasManyThrough(
            Batiment::class,
            Projet::class,
            'organisation_id', // clé étrangère sur Projet
            'projet_id',       // clé étrangère sur Batiment
            'id',              // clé locale sur Organisation
            'id'               // clé locale sur Projet
        );
    }


    
    public function devisEstimatif(): HasManyThrough
    {
        return $this->hasManyThrough(
            DevisEstimatif::class,
            Batiment::class,
            'projet_id',       // clé étrangère sur Batiment
            'batiment_id',     // clé étrangère sur DevisEstimatif
            'id',              // clé locale sur Organisation
            'id'               // clé locale sur Batiment
        )->join('projets', 'batiments.projet_id', '=', 'projets.id')
         ->where('projets.organisation_id', $this->id);
    }







    public function canBeDeleted(): bool
    {
        return
            !$this->is_system
            && !$this->organisationUsers()->exists()
            && !$this->projets()->exists();
    }
}
