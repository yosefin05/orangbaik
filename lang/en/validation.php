<?php

return [
    'required'  => ':attribute belum diisi.',
    'email'     => 'Format email belum benar. Contoh yang benar: nama@gmail.com',
    'unique'    => ':attribute ini sudah terdaftar. Coba gunakan yang lain, atau masuk ke akun kamu.',
    'confirmed' => 'Konfirmasi password belum sama. Pastikan kedua kolom password terisi sama persis.',
    'string'    => ':attribute harus berupa teks.',
    'lowercase' => ':attribute harus ditulis dengan huruf kecil semua.',

    'min' => [
        'string' => ':attribute minimal :min karakter.',
    ],
    'max' => [
        'string' => ':attribute terlalu panjang, maksimal :max karakter.',
    ],

    'password' => [
        'letters'       => 'Password harus mengandung minimal 1 huruf.',
        'mixed'         => 'Password harus mengandung huruf besar dan huruf kecil.',
        'numbers'       => 'Password harus mengandung minimal 1 angka.',
        'symbols'       => 'Password harus mengandung minimal 1 simbol, contoh: @ # !',
        'uncompromised' => 'Password ini terlalu umum dan sering bocor. Silakan pilih password lain.',
    ],

    // Nama kolom yang tampil di pesan
    'attributes' => [
        'name'                  => 'Nama lengkap',
        'email'                 => 'Email',
        'password'              => 'Password',
        'password_confirmation' => 'Konfirmasi password',
    ],
];