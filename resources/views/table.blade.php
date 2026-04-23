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
        <div class="card">
            <div class="card-header">
                <h3>Tabel Data</h3>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Deskripsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>Bundaran UGM</td>
                            <td>Jalan Pancasila</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>Monumen Jogja Kembali</td>
                            <td>Jl. Ring Road Utara</td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>Keraton Yogyakarta</td>
                            <td>Jl. Rotowijayan No. 1</td>
                        </tr>
                        <tr>
                            <td>4</td>
                            <td>Taman Sari</td>
                            <td>Jl. Taman Sari No. 2</td>
                        </tr>
                        <tr>
                            <td>5</td>
                            <td>Alun-Alun Yogyakarta</td>
                            <td>Jl. Alun-Alun Kidul</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
@endsection

@section('scripts')

@endsection