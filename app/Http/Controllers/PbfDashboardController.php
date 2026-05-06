<?php

namespace App\Http\Controllers;

use App\Models\Pbf;
use Illuminate\Http\Request;

class PbfDashboardController extends Controller
{
    public function index(Request $request)
    {
        $query = Pbf::query();

        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_pbf', 'like', "%{$search}%")
                  ->orWhere('alamat', 'like', "%{$search}%");
            });
        }

        $pbfs = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('pbf.index', compact('pbfs'));
    }

    public function create()
    {
        return view('pbf.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_pbf' => 'required|string|max:255',
            'nama_pbf' => 'required|string|max:255',
            'alamat' => 'required|string|max:255',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $data = $request->only(['kode_pbf', 'nama_pbf', 'alamat', 'lat', 'lng']);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('pbf', 'public');
        }

        Pbf::create($data);

        return redirect()->route('pbf.index')->with('success', 'PBF berhasil ditambahkan.');
    }

    public function edit(string $id)
    {
        $pbf = Pbf::findOrFail($id);

        return view('pbf.edit', compact('pbf'));
    }

    public function update(Request $request, string $id)
    {
        $pbf = Pbf::findOrFail($id);

        $request->validate([
            'kode_pbf' => 'required|string|max:255',
            'nama_pbf' => 'required|string|max:255',
            'alamat' => 'required|string|max:255',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $data = $request->only(['kode_pbf', 'nama_pbf', 'alamat', 'lat', 'lng']);

        if ($request->hasFile('image')) {
            if ($pbf->image_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($pbf->image_path);
            }
            $data['image_path'] = $request->file('image')->store('pbf', 'public');
        }

        $pbf->update($data);

        return redirect()->route('pbf.index')->with('success', 'PBF berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $pbf = Pbf::findOrFail($id);
        $pbf->delete();

        return redirect()->route('pbf.index')->with('success', 'PBF berhasil dihapus.');
    }
}
