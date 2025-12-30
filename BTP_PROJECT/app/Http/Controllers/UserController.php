<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
   
      public function index()
    {
        return Inertia::render('Users/Index', [
            'users' => User::select('id', 'name', 'email', 'created_at')
                ->latest()
                ->get(),
        ]);
    }

    public function show(User $user)
    {
        return Inertia::render('Users/Show', [
            'user' => $user->load('roles.permissions'),
        ]);
    }
}
