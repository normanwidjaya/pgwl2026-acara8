@extends('layouts.template')

@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.8/css/dataTables.dataTables.css">

    <style>
        body {
            margin: 0;
            padding: 0;
        }

        .data-card {
            margin-bottom: 30px;
        }

        .table img {
            width: 100px;
            height: 70px;
            object-fit: cover;
            border-radius: 6px;
        }
    </style>
@endsection

@section('content')
    <div class="container mt-3">

        {{-- Tabel Data Point --}}
        <div class="card data-card">
            <div class="card-header">
                <h3 class="mb-0">Tabel Data Point</h3>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="tabeldatapoints">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Deskripsi</th>
                                <th>Foto</th>
                                <th>Tanggal Dibuat</th>
                            </tr>
                        </thead>

                        <tbody>
                            @php
                                $no = 1;
                            @endphp

                            @foreach ($points as $p)
                                <tr>
                                    <td>{{ $no++ }}</td>
                                    <td>{{ $p['name'] }}</td>
                                    <td>{{ $p['description'] }}</td>
                                    <td>
                                        <img src="{{ asset('storage/images') . '/' . $p['image'] }}" alt="Foto">
                                    </td>
                                    <td>{{ $p['created_at'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Tabel Data Polyline --}}
        <div class="card data-card">
            <div class="card-header">
                <h3 class="mb-0">Tabel Data Polyline</h3>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="tabeldatapolylines">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Deskripsi</th>
                                <th>Foto</th>
                                <th>Tanggal Dibuat</th>
                            </tr>
                        </thead>

                        <tbody>
                            @php
                                $no = 1;
                            @endphp

                            @foreach ($polylines as $p)
                                <tr>
                                    <td>{{ $no++ }}</td>
                                    <td>{{ $p['name'] }}</td>
                                    <td>{{ $p['description'] }}</td>
                                    <td>
                                        <img src="{{ asset('storage/images') . '/' . $p['image'] }}" alt="Foto">
                                    </td>
                                    <td>{{ $p['created_at'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Tabel Data Polygon --}}
        <div class="card data-card">
            <div class="card-header">
                <h3 class="mb-0">Tabel Data Polygon</h3>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="tabeldatapolygons">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Deskripsi</th>
                                <th>Foto</th>
                                <th>Tanggal Dibuat</th>
                            </tr>
                        </thead>

                        <tbody>
                            @php
                                $no = 1;
                            @endphp

                            @foreach ($polygons as $p)
                                <tr>
                                    <td>{{ $no++ }}</td>
                                    <td>{{ $p['name'] }}</td>
                                    <td>{{ $p['description'] }}</td>
                                    <td>
                                        <img src="{{ asset('storage/images') . '/' . $p['image'] }}" alt="Foto">
                                    </td>
                                    <td>{{ $p['created_at'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.datatables.net/2.3.8/js/dataTables.js"></script>

    <script>
        new DataTable('#tabeldatapoints');
        new DataTable('#tabeldatapolylines');
        new DataTable('#tabeldatapolygons');
    </script>
@endsection
