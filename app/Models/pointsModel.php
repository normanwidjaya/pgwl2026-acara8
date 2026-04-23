<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class pointsModel extends Model
{
    protected $table = 'points';
    protected $guarded = ['id'];

public function geojson_points()
{
    $points = $this->select(DB::raw('id, ST_AsGeoJSON(geom) as geojson, name,
    description, image, created_at, updated_at'))->get();
    $geojson = [
    'type' => 'FeatureCollection',
    'features' => []

];

    foreach ($points as $p){
        $feature = [
            'type' => 'Feature',
            'geometry' => json_decode($p->geojson),
            'properties' => [
                'id' => $p->id,
                'name' => $p->name,
                'description' => $p->description,
                'image' => $p->image,
                'created_at' => $p->created_at,
                'updated_at' => $p->updated_at
            ]
        ];
        array_push($geojson['features'], $feature);
    }
    return $geojson;
}
}
