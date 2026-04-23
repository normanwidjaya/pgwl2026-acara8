@extends('layouts.template')
@section('styles')
    {{-- Leaflet JS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />


    {{-- Leaflet Draw JS --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css" />

    <style>
        body {
            margin: 0;
            padding: 0;
        }

        #map {
            height: 100vh;
            width: 100%;
        }

        /* ensure modal title is visible */
        .modal-header .modal-title {
            color: black !important;
        }

        /* Layer control styling */
        .leaflet-control-layers {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 14px;
        }

        .leaflet-control-layers-toggle {
            background-color: rgba(255, 255, 255, 0.9);
            border: 2px solid #ccc;
        }

        .leaflet-control-layers-list {
            background-color: rgba(255, 255, 255, 0.95);
        }

        .leaflet-control-layers-overlays label {
            margin: 5px 0;
            padding: 2px 5px;
            border-radius: 3px;
            transition: background-color 0.2s;
        }

        .leaflet-control-layers-overlays label:hover {
            background-color: rgba(0, 123, 255, 0.1);
        }
    </style>
@endsection


@section('content')
    <div id="map"></div>

    {{-- Modal Form Input Point --}}
    <div class="modal fade" tabindex="-1" id="modalInputPoint" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Input Point</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('points.store') }}" method="post">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name"
                                placeholder="Fill name">
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="geometry_point" class="form-label">Geometry</label>
                            <textarea class="form-control" id="geometry_point" name="geometry_point" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Form Input Polyline --}}
    <div class="modal fade" tabindex="-1" id="modalInputPolyline" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Input Polyline</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('polylines.store') }}" method="post">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name"
                                placeholder="Fill name">
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="geometry_polyline" class="form-label">Geometry</label>
                            <textarea class="form-control" id="geometry_polyline" name="geometry_polyline" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Form Input Polygon --}}
    <div class="modal fade" tabindex="-1" id="modalInputPolygon" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Input Polygon</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('polygons.store') }}" method="post">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name"
                                placeholder="Fill name">
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="geometry_polygon" class="form-label">Geometry</label>
                            <textarea class="form-control" id="geometry_polygon" name="geometry_polygon" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    {{-- Leaflet JS --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>


    {{-- Leaflet Draw JS --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>

    {{-- Terraformer JS --}}
    <script src="https://unpkg.com/@terraformer/wkt"></script>

    {{-- JQuery JS --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>


    <script>
        // Inisialisasi peta dan atur tampilan ke koordinat Yogyakarta dengan tingkat zoom
        var map = L.map('map').setView([-7.7956, 110.3695], 13);

        // Tambahkan tile layer OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        // Create custom marker icon for points
        const pointIcon = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });

        // Load existing points data
        fetch('/api/points')
            .then(response => response.json())
            .then(data => {
                L.geoJSON(data, {
                    onEachFeature: function(feature, layer) {
                        let popupContent = '<b>' + feature.properties.name + '</b><br>' +
                            feature.properties.description;
                        if (feature.properties.image) {
                            popupContent += '<br><img src="' + feature.properties.image +
                                '" style="max-width: 150px; margin-top: 5px;">';
                        }
                        layer.bindPopup(popupContent);
                    },
                    pointToLayer: function(feature, latlng) {
                        return L.marker(latlng, {
                            icon: pointIcon
                        });
                    }
                }).addTo(map);
            })
            .catch(error => console.error('Error loading points:', error));

        // Load existing polylines data
        fetch('/api/polylines')
            .then(response => response.json())
            .then(data => {
                L.geoJSON(data, {
                    onEachFeature: function(feature, layer) {
                        layer.bindPopup('<b>' + feature.properties.name + '</b><br>' + feature.properties
                            .description);
                    },
                    style: function(feature) {
                        return {
                            color: '#0066ff',
                            weight: 3,
                            opacity: 0.8
                        };
                    }
                }).addTo(map);
            })
            .catch(error => console.error('Error loading polylines:', error));

        // Load existing polygons data
        fetch('/api/polygons')
            .then(response => response.json())
            .then(data => {
                L.geoJSON(data, {
                    onEachFeature: function(feature, layer) {
                        layer.bindPopup('<b>' + feature.properties.name + '</b><br>' + feature.properties
                            .description);
                    },
                    style: function(feature) {
                        return {
                            color: '#ff6600',
                            weight: 2,
                            opacity: 0.8,
                            fillColor: '#ffcc99',
                            fillOpacity: 0.3
                        };
                    }
                }).addTo(map);
            })
            .catch(error => console.error('Error loading polygons:', error));

        // Digitize Function
        var drawnItems = new L.FeatureGroup();
        map.addLayer(drawnItems);

        var drawControl = new L.Control.Draw({
            draw: {
                position: 'topleft',
                polyline: true,
                polygon: true,
                rectangle: true,
                circle: false,
                marker: true,
                circlemarker: false
            },
            edit: false
        });

        map.addControl(drawControl);

        map.on('draw:created', function(e) {
            var type = e.layerType,
                layer = e.layer;

            console.log(type);
            console.log('draw event fired for marker?');

            var drawnJSONObject = layer.toGeoJSON();
            var objectGeometry = Terraformer.geojsonToWKT(drawnJSONObject.geometry);

            console.log(drawnJSONObject);
            console.log(objectGeometry);

            if (type === 'polyline') {
                // Set value geometry to geometry_polyline textarea
                $('#geometry_polyline').val(objectGeometry);

                // Show Modal Input Polyline using Bootstrap 5 API
                var modalEl = document.getElementById('modalInputPolyline');
                if (typeof bootstrap === 'undefined') {
                    console.error('bootstrap does not exist');
                } else {
                    console.log('bootstrap found, creating modal');
                    // set a title just before showing
                    modalEl.querySelector('.modal-title').textContent = 'Input Polyline';
                    var bsModal = new bootstrap.Modal(modalEl);
                    bsModal.show();
                }

                // Modal dismiss reload page
                $('#modalInputPolyline').on('hidden.bs.modal', function() {
                    location.reload();
                });
            } else if (type === 'polygon' || type === 'rectangle') {
                // Set value geometry to geometry_polygon textarea
                $('#geometry_polygon').val(objectGeometry);

                // Show Modal Input Polygon using Bootstrap 5 API
                var modalEl = document.getElementById('modalInputPolygon');
                if (typeof bootstrap === 'undefined') {
                    console.error('bootstrap does not exist');
                } else {
                    console.log('bootstrap found, creating modal');
                    // set a title just before showing
                    modalEl.querySelector('.modal-title').textContent = 'Input Polygon';
                    var bsModal = new bootstrap.Modal(modalEl);
                    bsModal.show();
                }

                // Modal dismiss reload page
                $('#modalInputPolygon').on('hidden.bs.modal', function() {
                    location.reload();
                });
            } else if (type === 'marker') {
                // Set value geometry to geometry_point textarea
                $('#geometry_point').val(objectGeometry);

                // Show Modal Input Point using Bootstrap 5 API
                var modalEl = document.getElementById('modalInputPoint');
                if (typeof bootstrap === 'undefined') {
                    console.error('bootstrap does not exist');
                } else {
                    console.log('bootstrap found, creating modal');
                    // set a title just before showing
                    modalEl.querySelector('.modal-title').textContent = 'Input Point';
                    var bsModal = new bootstrap.Modal(modalEl);
                    bsModal.show();
                }

                // Modal dismiss reload page
                $('#modalInputPoint').on('hidden.bs.modal', function() {
                    location.reload();
                });
            } else {
                console.log('__undefined__');
            }

            drawnItems.addLayer(layer);
        });
        //Points Layer

        var points = L.geoJSON(null, {
            onEachFeature: function(feature, layer) {
                var popup_content = "Nama: " + feature.properties.name + "<br>" +
                    "Deskripsi: " + feature.properties.description + "<br>" +
                    "Dibuat: " + feature.properties.created_at;

                layer.on({
                    click: function(e) {
                        layer.bindPopup(popup_content).openPopup();
                    }
                });
            }
        });

        // ✅ letakkan di luar
        $.getJSON("{{ route('geojson.points') }}", function(data) {
            points.addData(data);
            map.addLayer(points);
        });
        //Polylines Layer

        var polylines = L.geoJSON(null, {
            onEachFeature: function(feature, layer) {
                var popup_content = "Nama: " + feature.properties.name + "<br>" +
                    "Deskripsi: " + feature.properties.description + "<br>" +
                    "Dibuat: " + feature.properties.created_at;

                layer.on({
                    click: function(e) {
                        layer.bindPopup(popup_content).openPopup();
                    }
                });
            }
        });

        // ✅ letakkan di luar
        $.getJSON("{{ route('geojson.polylines') }}", function(data) {
            polylines.addData(data);
            map.addLayer(polylines);
        });
        //Polygons Layer

        var polygons = L.geoJSON(null, {
            onEachFeature: function(feature, layer) {
                var popup_content = "Nama: " + feature.properties.name + "<br>" +
                    "Deskripsi: " + feature.properties.description + "<br>" +
                    "Dibuat: " + feature.properties.created_at;

                layer.on({
                    click: function(e) {
                        layer.bindPopup(popup_content).openPopup();
                    }
                });
            }
        });

        // ✅ letakkan di luar
        $.getJSON("{{ route('geojson.polygons') }}", function(data) {
            polygons.addData(data);
            map.addLayer(polygons);
        });

        // Control Layer
        var baseMaps = {

        };

        var overlayMaps = {
            "Points": points,
            "Polylines": polylines,
            "Polygons": polygons,
        };

        var controllayer = L.control.layers(baseMaps, overlayMaps);
        controllayer.addTo(map);
    </script>
@endsection
