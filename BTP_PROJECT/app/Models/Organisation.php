<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organisation extends Model
{
    use HasFactory;

    protected $fillable = ['raison_sociale', 'logo', 'adresse', 'pays', 'devise', 'user_id'];

    // Une organisation appartient à un utilisateur (créateur)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Une organisation a plusieurs membres
    public function organisationUsers()
    {
        return $this->hasMany(OrganisationUser::class);
    }

  
}
