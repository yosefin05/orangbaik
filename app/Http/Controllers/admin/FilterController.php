<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Filter;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FilterController extends Controller
{
    public function index()
    {
        $filters = Filter::latest()->paginate(10);

        return view(
            'admin.filter.index',
            compact('filters')
        );
    }

    public function create()
    {
        return view('admin.filter.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_filter' => 'required|max:255'
        ]);

        Filter::create([
            'nama_filter' => $request->nama_filter,
            'slug' => Str::slug($request->nama_filter)
        ]);

        return redirect()
            ->route('admin.filter.index')
            ->with('success', 'Filter berhasil ditambahkan');
    }

    public function edit(Filter $filter)
    {
        return view(
            'admin.filter.edit',
            compact('filter')
        );
    }

    public function update(Request $request, Filter $filter)
    {
        $request->validate([
            'nama_filter' => 'required|max:255'
        ]);

        $filter->update([
            'nama_filter' => $request->nama_filter,
            'slug' => Str::slug($request->nama_filter)
        ]);

        return redirect()
            ->route('admin.filter.index')
            ->with('success', 'Filter berhasil diperbarui');
    }

    public function destroy(Filter $filter)
    {
        $filter->delete();

        return redirect()
            ->route('admin.filter.index')
            ->with('success', 'Filter berhasil dihapus');
    }
}