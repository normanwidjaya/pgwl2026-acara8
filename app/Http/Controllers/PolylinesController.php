<?php

namespace App\Http\Controllers;

use App\Models\polylinesModel;
use Illuminate\Http\Request;

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

        $data = [
            'geom' => $request->geometry_polyline,
            'name' => $request->name,
            'description' => $request->description,
            'image' => $name_image
        ];

        // Simpan data ke database
        if (!$this->polylines->create($data)) {
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
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
