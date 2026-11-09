<?php

return [
    'required'  => ':attribute wajib diisi.',
    'email'     => 'Format email tidak valid. Contoh: nama@gmail.com',
    'unique'    => ':attribute sudah dipakai. Coba yang lain, atau langsung masuk.',
    'confirmed' => 'Password dan konfirmasinya tidak sama.',
    'string'    => ':attribute harus berupa teks.',
    'lowercase' => ':attribute harus huruf kecil semua.',

    'min' => [
        'string' => ':attribute minimal :min karakter.',
    ],
    'max' => [
        'string' => ':attribute maksimal :max karakter.',
    ],

    'password' => [
        'letters'       => 'Password harus ada hurufnya.',
        'mixed'         => 'Password harus ada huruf besar dan huruf kecil.',
        'numbers'       => 'Password harus ada angkanya.',
        'symbols'       => 'Password harus ada simbol, contoh: @ # !',
        'uncompromised' => 'Password ini terlalu mudah ditebak. Pilih yang lain.',
    ],

    'attributes' => [
        'name'                  => 'Nama',
        'email'                 => 'Email',
        'password'              => 'Password',
        'password_confirmation' => 'Konfirmasi password',
    ],
];