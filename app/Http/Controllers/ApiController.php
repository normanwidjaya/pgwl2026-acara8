<?php

namespace App\Http\Controllers;

use App\Models\polygonsModel;
use App\Models\polylinesModel;
use App\Models\pointsModel;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function __construct()
    {
        $this->points = new pointsModel();
        $this->polylines = new polylinesModel();
        $this->polygons = new polygonsModel();
    }
public function geojson_points()
{
    $points = $this->points->geojson_points();
    return response()->json($points, 200, [], JSON_NUMERIC_CHECK);
}
public function geojson_polylines()
{
    $polylines = $this->polylines->geojson_polylines();
    return response()->json($polylines, 200, [], JSON_NUMERIC_CHECK);
}
public function geojson_polygons()
{
    $polygons = $this->polygons->geojson_polygons();
    return response()->json($polygons, 200, [], JSON_NUMERIC_CHECK);
}

}
