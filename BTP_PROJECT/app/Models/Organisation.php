<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function canBeDeleted(): bool
    {
        return
            !$this->is_system
            && !$this->organisationUsers()->exists()
            && !$this->projets()->exists();
    }

}
