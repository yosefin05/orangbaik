<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Testimoni;
use Illuminate\Http\Request;

class TestimoniController extends Controller
{
    public function index()
    {
        $testimoni = Testimoni::with('user')
            ->latest()
            ->paginate(10);

        return view(
            'admin.testimoni.index',
            compact('testimoni')
        );
    }

    public function create()
    {
        return view(
            'admin.testimoni.create'
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'foto_profil' => 'required|image|max:2048',
            'nama' => 'required|max:255',
            'jabatan' => 'required|max:255',
            'isi_testimoni' => 'required',
        ]);

        $foto = $request->file('foto_profil')
            ->store('testimoni', 'public');

        Testimoni::create([
            'user_id' => auth()->id(),
            'foto_profil' => $foto,
            'nama' => $request->nama,
            'jabatan' => $request->jabatan,
            'isi_testimoni' => $request->isi_testimoni,
        ]);

        return redirect()
            ->route('admin.testimoni.index')
            ->with(
                'success',
                'Testimoni berhasil ditambahkan.'
            );
    }

    /**
     * Display the specified resource.
     */
    public function show(Testimoni $testimoni)
    {
        return view(
            'admin.testimoni.show',
            compact('testimoni')
        );
    }

    public function edit(Testimoni $testimoni)
    {
        return view(
            'admin.testimoni.edit',
            compact('testimoni')
        );
    }

    public function update(Testimoni $testimoni, Request $request)
    {
        $request->validate([
            'nama' => 'required|max:255',
            'jabatan' => 'required|max:255',
            'isi_testimoni' => 'required',
            'foto_profil' => 'nullable|image|max:2048',
        ]);

        $data = [
            'nama' => $request->nama,
            'jabatan' => $request->jabatan,
            'isi_testimoni' => $request->isi_testimoni,
        ];

        if ($request->hasFile('foto_profil')) {

            $foto = $request->file('foto_profil')
                ->store('testimoni', 'public');

            $data['foto_profil'] = $foto;
        }

        $testimoni->update($data);

        return redirect()
            ->route('admin.testimoni.index')
            ->with(
                'success',
                'Testimoni berhasil diperbarui.'
            );
    }
    public function destroy(Testimoni $testimoni)
    {
        $testimoni->delete();

        return redirect()
            ->route('admin.testimoni.index')
            ->with(
                'success',
                'Testimoni berhasil dihapus.'
            );
    }
}
