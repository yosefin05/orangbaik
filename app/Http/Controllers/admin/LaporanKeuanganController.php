<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LaporanKeuangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class LaporanKeuanganController extends Controller
{
    public function index()
    {
        $reports = LaporanKeuangan::orderBy('tahun', 'desc')->paginate(15);
        return view('admin.laporan-keuangan.index', compact('reports'));
    }

    public function create()
    {
        return view('admin.laporan-keuangan.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'tahun'     => 'required|integer|digits:4|unique:laporan_keuangan,tahun',
            'judul'     => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'file'      => 'required|file|mimes:pdf,jpg,jpeg,png,webp|max:15360',
            'urutan'    => 'nullable|integer',
            'is_active' => 'boolean',
        ], [
            'tahun.unique' => 'Laporan keuangan untuk tahun tersebut sudah ada.',
            'file.mimes'   => 'File laporan disarankan berupa PDF atau gambar.',
            'file.max'     => 'Ukuran file maksimal 15MB.',
        ]);

        $filePath = $request->file('file')->store('laporan_keuangan', 'public');

        LaporanKeuangan::create([
            'tahun'     => $request->tahun,
            'judul'     => $request->judul,
            'deskripsi' => $request->deskripsi,
            'file_path' => $filePath,
            'urutan'    => $request->input('urutan', 0),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.laporan-keuangan.index')
            ->with('success', 'Laporan keuangan berhasil ditambahkan.');
    }

    public function edit(LaporanKeuangan $laporan_keuangan)
    {
        return view('admin.laporan-keuangan.edit', ['report' => $laporan_keuangan]);
    }

    public function update(Request $request, LaporanKeuangan $laporan_keuangan)
    {
        $request->validate([
            'tahun'     => ['required', 'integer', 'digits:4', Rule::unique('laporan_keuangan', 'tahun')->ignore($laporan_keuangan->id)],
            'judul'     => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'file'      => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:15360',
            'urutan'    => 'nullable|integer',
            'is_active' => 'boolean',
        ], [
            'tahun.unique' => 'Laporan keuangan untuk tahun tersebut sudah ada.',
        ]);

        $data = [
            'tahun'     => $request->tahun,
            'judul'     => $request->judul,
            'deskripsi' => $request->deskripsi,
            'urutan'    => $request->input('urutan', 0),
            'is_active' => $request->boolean('is_active', true),
        ];

        if ($request->hasFile('file')) {
            if ($laporan_keuangan->file_path && Storage::disk('public')->exists($laporan_keuangan->file_path)) {
                Storage::disk('public')->delete($laporan_keuangan->file_path);
            }
            $data['file_path'] = $request->file('file')->store('laporan_keuangan', 'public');
        }

        $laporan_keuangan->update($data);

        return redirect()->route('admin.laporan-keuangan.index')
            ->with('success', 'Laporan keuangan berhasil diperbarui.');
    }

    public function destroy(LaporanKeuangan $laporan_keuangan)
    {
        if ($laporan_keuangan->file_path && Storage::disk('public')->exists($laporan_keuangan->file_path)) {
            Storage::disk('public')->delete($laporan_keuangan->file_path);
        }

        $laporan_keuangan->delete();

        return redirect()->route('admin.laporan-keuangan.index')
            ->with('success', 'Laporan keuangan berhasil dihapus.');
    }

    public function toggleActive(LaporanKeuangan $laporan_keuangan)
    {
        $laporan_keuangan->update(['is_active' => !$laporan_keuangan->is_active]);

        return back()->with('success', 'Status aktif laporan keuangan berhasil diubah.');
    }
}
