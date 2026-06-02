<?php

namespace App\Http\Controllers;

use App\Models\pointsModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PointsController extends Controller
{
    protected $points;
    public function __construct()
    {
        $this->points = new pointsModel();
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
        $request->validate(
            [
                'geometry_point' => 'required',
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
            ],
            [
                'geometry_point.required' => 'Field geometry point harus diisi.',
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
            $name_image = time() . "_point." . strtolower($image->getClientOriginalExtension());
            $image->move('storage/images', $name_image);
        } else {
            $name_image = null;
        }

        // Simpan data ke database (gunakan ST_GeomFromGeoJSON untuk konversi)
        $inserted = DB::insert('INSERT INTO points (geom, name, description, image, created_at, updated_at) VALUES (ST_GeomFromGeoJSON(?), ?, ?, ?, NOW(), NOW())', [
            $request->geometry_point,
            $request->name,
            $request->description,
            $name_image
        ]);

        if (!$inserted) {
            return redirect()->route('peta')->with('error', 'Gagal menyimpan data point.');
        }

        // Kembali ke halaman peta
        return redirect()->route('peta')->with('success', 'Data point berhasil disimpan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $point = $this->points->find($id);
        if (!$point) {
            return response()->json(['message' => 'Data point tidak ditemukan.'], 404);
        }

        return response()->json([
            'id' => $point->id,
            'name' => $point->name,
            'description' => $point->description,
            'geom' => $point->geom,
            'image' => $point->image,
            'created_at' => $point->created_at,
            'updated_at' => $point->updated_at,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $point = $this->points->find($id);
        if (!$point) {
            return redirect()->route('peta')->with('error', 'Data point tidak ditemukan.');
        }

        $data = [
            'title' => 'Edit Point',
            'point' => $point,
        ];

        return view('point-edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $point = $this->points->find($id);
        if (!$point) {
            return response()->json(['message' => 'Data point tidak ditemukan.'], 404);
        }

        // Validasi input
        $request->validate(
            [
                'geometry_point' => 'required',
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
            ],
            [
                'geometry_point.required' => 'Field geometry point harus diisi.',
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
            mkdir('./storage/images', 0777);
        }

        // Get the uploaded image
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($point->image) {
                $oldImagePath = base_path('storage/images/' . $point->image);
                if (file_exists($oldImagePath)) {
                    @unlink($oldImagePath);
                }
            }

            $image = $request->file('image');
            $name_image = time() . "_point." . strtolower($image->getClientOriginalExtension());
            $image->move('storage/images', $name_image);
        } else {
            $name_image = $point->image; // Keep old image
        }

        // Simpan update data ke database (gunakan ST_GeomFromGeoJSON untuk konversi)
        $affected = DB::update('UPDATE points SET geom = ST_GeomFromGeoJSON(?), name = ?, description = ?, image = ?, updated_at = NOW() WHERE id = ?', [
            $request->geometry_point,
            $request->name,
            $request->description,
            $name_image,
            $id
        ]);

        if ($affected === 0) {
            return response()->json(['message' => 'Gagal memperbarui data point.'], 500);
        }

        return response()->json(['message' => 'Data point berhasil diperbarui.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $point = $this->points->find($id);
        if (!$point) {
            return response()->json(['message' => 'Data point tidak ditemukan.'], 404);
        }

        if ($point->image) {
            $imagePath = base_path('storage/images/' . $point->image);
            if (file_exists($imagePath)) {
                @unlink($imagePath);
            }
        }

        if (!$point->delete()) {
            return response()->json(['message' => 'Gagal menghapus data point.'], 500);
        }

        return response()->json(['message' => 'Data point berhasil dihapus.']);
    }
}
