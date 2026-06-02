<?php

namespace App\Http\Controllers;

use App\Models\pointsModel;
use App\Models\polylinesModel;
use App\Models\polygonsModel;
use App\Models\User;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function __construct()
    {
            $this->points = new pointsModel();
            $this->polylines = new polylinesModel();
            $this->polygons = new polygonsModel();
            $this->users = new User();
    }

    public function landingpage()
    {
        $data = [
            'title' => 'PGWL',
            'points_count' => $this->points->count(),
            'polylines_count' => $this->polylines->count(),
            'polygons_count' => $this->polygons->count(),
            'users_count' => $this->users->count(),
        ];

        return view('home', $data);
    }
    public function peta()
    {
        $data = [
            'title' => 'Peta',
        ];

        return view('map', $data);
    }

    public function tabel()
    {
        $data = [
            'title' => 'Tabel',
            'points' => $this->points->all(),
            'polylines' => $this->polylines->all(),
            'polygons' => $this->polygons->all(),
        ];

        return view('table', $data);
    }
}
