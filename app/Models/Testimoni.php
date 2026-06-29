<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Testimoni extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function showLoginForm()
    {
        $testimoni = Testimoni::inRandomOrder()->first();

        return view('auth.login', [
            'testimoni' => $testimoni,
        ]);
    }

    public function showRegistrationForm()
    {
        $testimoni = Testimoni::inRandomOrder()->first();

        return view('auth.register', [
            'testimoni' => $testimoni,
        ]);
    }
    protected $table = 'testimoni';
    protected $fillable = [
        'foto_profil',
        'nama',
        'jabatan',
        'isi_testimoni',
        'user_id'
    ];
}
