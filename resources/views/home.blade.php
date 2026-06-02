@extends('layouts.template')

@section('styles')
    <style>
        body {
            margin: 0;
            padding: 0;
        }
    </style>
@endsection

@section('content')
    <div class="container mt-3">

        <div class="card mb-3">
            <div class="card-header">
                <h3 class="mb-0">Tabel Data</h3>
            </div>
            <div class="card-body">
                <p class="mb-0">
                    Aplikasi ini dibuat untuk memenuhi tugas mata kuliah Praktikum Pemrograman Geospasial Web Lanjut.
                    Aplikasi ini menampilkan peta interaktif yang menunjukkan objek dengan geometri titik, garis, dan area
                    yang dapat ditambah, ditampilkan, diubah, dan dihapus. Aplikasi ini dikembangkan dengan menggunakan
                    Laravel dan PostgreSQL - PostGIS.
                </p>
            </div>
        </div>

        <div class="row mt-3">

            <div class="col-3">
                <div class="card">
                    <div class="card-header">
                        <h3>Jumlah Points</h3>
                    </div>
                    <div class="card-body text-center">
                        <h1 >
                            {{ $points_count }}
                        </h1>
                    </div>
                </div>
            </div>

            <div class="col-3">
                <div class="card">
                    <div class="card-header">
                        <h3>Jumlah Polyline</h3>
                    </div>
                    <div class="card-body text-center">
                        <h1 >
                            {{ $polylines_count }}
                        </h1>
                    </div>
                </div>
            </div>

            <div class="col-3">
                <div class="card">
                    <div class="card-header">
                        <h3>Jumlah Polygons</h3>
                    </div>
                    <div class="card-body text-center">
                        <h1 >
                            {{ $polygons_count }}
                        </h1>
                    </div>
                </div>
            </div>

            <div class="col-3">
                <div class="card">
                    <div class="card-header">
                        <h3>Jumlah User</h3>
                    </div>
                    <div class="card-body text-center">
                        <h1>
                            {{ $users_count }}
                        </h1>
                    </div>
                </div>
            </div>

        </div>

    </div>
@endsection

@section('scripts')
@endsection
