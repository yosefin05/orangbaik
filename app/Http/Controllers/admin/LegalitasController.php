<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Legalitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LegalitasController extends Controller
{
    public function index()
    {
        $legalitas = Legalitas::orderBy('urutan', 'asc')->paginate(15);
        return view('admin.legalitas.index', compact('legalitas'));
    }

    public function create()
    {
        return view('admin.legalitas.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul'           => 'required|string|max:255',
            'nomor_legalitas' => 'nullable|string|max:255',
            'deskripsi'       => 'nullable|string',
            'file'            => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:10240',
            'urutan'          => 'nullable|integer',
            'is_active'       => 'boolean',
        ]);

        $filePath = $request->file('file')->store('legalitas', 'public');

        Legalitas::create([
            'judul'           => $request->judul,
            'nomor_legalitas' => $request->nomor_legalitas,
            'deskripsi'       => $request->deskripsi,
            'file_path'       => $filePath,
            'urutan'          => $request->input('urutan', 0),
            'is_active'       => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.legalitas.index')
            ->with('success', 'Data legalitas berhasil ditambahkan.');
    }

    public function edit(Legalitas $legalita)
    {
        return view('admin.legalitas.edit', ['legalitas' => $legalita]);
    }

    public function update(Request $request, Legalitas $legalita)
    {
        $request->validate([
            'judul'           => 'required|string|max:255',
            'nomor_legalitas' => 'nullable|string|max:255',
            'deskripsi'       => 'nullable|string',
            'file'            => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:10240',
            'urutan'          => 'nullable|integer',
            'is_active'       => 'boolean',
        ]);

        $data = [
            'judul'           => $request->judul,
            'nomor_legalitas' => $request->nomor_legalitas,
            'deskripsi'       => $request->deskripsi,
            'urutan'          => $request->input('urutan', 0),
            'is_active'       => $request->boolean('is_active', true),
        ];

        if ($request->hasFile('file')) {
            if ($legalita->file_path && Storage::disk('public')->exists($legalita->file_path)) {
                Storage::disk('public')->delete($legalita->file_path);
            }
            $data['file_path'] = $request->file('file')->store('legalitas', 'public');
        }

        $legalita->update($data);

        return redirect()->route('admin.legalitas.index')
            ->with('success', 'Data legalitas berhasil diperbarui.');
    }

    public function destroy(Legalitas $legalita)
    {
        if ($legalita->file_path && Storage::disk('public')->exists($legalita->file_path)) {
            Storage::disk('public')->delete($legalita->file_path);
        }

        $legalita->delete();

        return redirect()->route('admin.legalitas.index')
            ->with('success', 'Data legalitas berhasil dihapus.');
    }

    public function toggleActive(Legalitas $legalita)
    {
        $legalita->update(['is_active' => !$legalita->is_active]);

        return back()->with('success', 'Status aktif legalitas berhasil diubah.');
    }
}
