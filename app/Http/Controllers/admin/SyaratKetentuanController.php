<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\SyaratKetentuan;
use Illuminate\Http\Request;

class SyaratKetentuanController extends Controller
{
    public function index()
    {
        $items = SyaratKetentuan::query()
            ->orderBy('urutan')
            ->orderBy('id')
            ->paginate(10);

        return view('admin.syarat-ketentuan.index', compact('items'));
    }

    public function create()
    {
        return view('admin.syarat-ketentuan.create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['urutan'] = $data['urutan'] ?? $this->nextUrutan();
        $data['is_active'] = $request->boolean('is_active');

        SyaratKetentuan::create($data);

        return redirect()
            ->route('admin.syarat-ketentuan.index')
            ->with('success', 'Bagian syarat dan ketentuan berhasil ditambahkan.');
    }

    public function edit(SyaratKetentuan $syarat_ketentuan)
    {
        return view('admin.syarat-ketentuan.edit', [
            'item' => $syarat_ketentuan,
        ]);
    }

    public function update(Request $request, SyaratKetentuan $syarat_ketentuan)
    {
        $data = $this->validated($request);
        $data['is_active'] = $request->boolean('is_active');

        $syarat_ketentuan->update($data);

        return redirect()
            ->route('admin.syarat-ketentuan.index')
            ->with('success', 'Bagian syarat dan ketentuan berhasil diperbarui.');
    }

    public function destroy(SyaratKetentuan $syarat_ketentuan)
    {
        $syarat_ketentuan->delete();

        return redirect()
            ->route('admin.syarat-ketentuan.index')
            ->with('success', 'Bagian syarat dan ketentuan berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'isi' => ['required', 'string'],
            'urutan' => ['nullable', 'integer', 'min:0'],
        ]);
    }

    private function nextUrutan(): int
    {
        return (int) SyaratKetentuan::query()->max('urutan') + 1;
    }
}
