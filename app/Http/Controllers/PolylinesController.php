<?php

namespace App\Http\Controllers;

use App\Models\polylinesModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PolylinesController extends Controller
{
    protected $polylines;
    public function __construct()
    {
        $this->polylines = new polylinesModel();
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'geometry_polyline' => 'required',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ],
        [ 'geometry_polyline.required' => 'Field geometry polyline harus diisi.',
            'name.required' => 'Field name harus diisi.',
            'name.string' => 'Field name harus berupa string.',
            'name.max' => 'Field name tidak boleh melebihi 255 karakter.',
            'description.required' => 'Field description harus diisi.',
            'description.string' => 'Field description harus berupa string.',
            'image.image' => 'File yang diunggah harus berupa gambar.',
            'image.mimes' => 'File yang diunggah harus berupa file dengan ekstensi: jpeg, png, jpg.',
            'image.max' => 'Ukuran file tidak boleh melebihi 2MB.',
        ]
        );

        // Create directory for images if it doesn't exist
        if (!is_dir('storage/images')) {
            mkdir('./storage/images', 0777, true);
        }

        // Get the uploaded image
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $name_image = time() . "_polyline." . strtolower($image->getClientOriginalExtension());
            $image->move('storage/images', $name_image);
        } else {
            $name_image = null;
        }

        // Simpan data ke database (gunakan ST_GeomFromGeoJSON)
        $inserted = DB::insert('INSERT INTO polylines (geom, name, description, image, created_at, updated_at) VALUES (ST_GeomFromGeoJSON(?), ?, ?, ?, NOW(), NOW())', [
            $request->geometry_polyline,
            $request->name,
            $request->description,
            $name_image
        ]);

        if (!$inserted) {
            return redirect()->route('peta')->with('error', 'Gagal menyimpan data polyline.');
        }

        // Kembali ke halaman peta
        return redirect()->route('peta')->with('success', 'Data polyline berhasil disimpan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $polyline = $this->polylines->find($id);
        if (!$polyline) {
            return response()->json(['message' => 'Data polyline tidak ditemukan.'], 404);
        }

        return response()->json([
            'id' => $polyline->id,
            'name' => $polyline->name,
            'description' => $polyline->description,
            'geom' => $polyline->geom,
            'image' => $polyline->image,
            'created_at' => $polyline->created_at,
            'updated_at' => $polyline->updated_at,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $polyline = $this->polylines->find($id);
        if (!$polyline) {
            return redirect()->route('peta')->with('error', 'Data polyline tidak ditemukan.');
        }

        return view('polyline-edit', [
            'title' => 'Edit Polyline',
            'polyline' => $polyline,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $polyline = $this->polylines->find($id);
        if (!$polyline) {
            return response()->json(['message' => 'Data polyline tidak ditemukan.'], 404);
        }

        // Validasi input
        $request->validate([
            'geometry_polyline' => 'required',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ],
        [ 'geometry_polyline.required' => 'Field geometry polyline harus diisi.',
            'name.required' => 'Field name harus diisi.',
            'name.string' => 'Field name harus berupa string.',
            'name.max' => 'Field name tidak boleh melebihi 255 karakter.',
            'description.required' => 'Field description harus diisi.',
            'description.string' => 'Field description harus berupa string.',
            'image.image' => 'File yang diunggah harus berupa gambar.',
            'image.mimes' => 'File yang diunggah harus berupa file dengan ekstensi: jpeg, png, jpg.',
            'image.max' => 'Ukuran file tidak boleh melebihi 2MB.',
        ]
        );

        // Create directory for images if it doesn't exist
        if (!is_dir('storage/images')) {
            mkdir('./storage/images', 0777, true);
        }

        // Get the uploaded image
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($polyline->image) {
                $oldImagePath = base_path('storage/images/' . $polyline->image);
                if (file_exists($oldImagePath)) {
                    @unlink($oldImagePath);
                }
            }

            $image = $request->file('image');
            $name_image = time() . "_polyline." . strtolower($image->getClientOriginalExtension());
            $image->move('storage/images', $name_image);
        } else {
            $name_image = $polyline->image; // Keep old image
        }

        // Update data ke database (gunakan ST_GeomFromGeoJSON)
        $affected = DB::update('UPDATE polylines SET geom = ST_GeomFromGeoJSON(?), name = ?, description = ?, image = ?, updated_at = NOW() WHERE id = ?', [
            $request->geometry_polyline,
            $request->name,
            $request->description,
            $name_image,
            $id
        ]);

        if ($affected === 0) {
            return response()->json(['message' => 'Gagal mengupdate data polyline.'], 500);
        }

        return response()->json(['message' => 'Data polyline berhasil diupdate.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $polyline = $this->polylines->find($id);
        if (!$polyline) {
            return response()->json(['message' => 'Data polyline tidak ditemukan.'], 404);
        }

        if ($polyline->image) {
            $imagePath = base_path('storage/images/' . $polyline->image);
            if (file_exists($imagePath)) {
                @unlink($imagePath);
            }
        }

        if (!$polyline->delete()) {
            return response()->json(['message' => 'Gagal menghapus data polyline.'], 500);
        }

        return response()->json(['message' => 'Data polyline berhasil dihapus.']);
    }
}
