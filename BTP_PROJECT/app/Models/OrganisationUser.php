<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class OrganisationUser extends Model
{
    use HasFactory;
    protected $table = 'organisation_users';
    protected $fillable = ['user_id',  'organisation_id', 'role_id'];




    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }
       public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
