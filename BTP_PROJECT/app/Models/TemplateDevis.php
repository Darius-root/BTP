<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemplateDevis extends Model
{
     protected $table = 'template_devis';
     protected $fillable = [
        'nom',
        'description',
        'created_by',
    ];




      public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function devis()
    {
        return $this->hasMany(DevisEstimatif::class, 'template_id');
    }
}
