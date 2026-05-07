@extends('layouts.template')
@section('styles')
    {{-- Leaflet JS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />


    {{-- Leaflet Draw JS --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css" />

    <style>
        body, html {
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
        }

        #map {
            height: calc(100vh - 56px);
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

    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1050; margin-bottom: 20px;">
        <div id="liveToastDeleteSuccess" class="toast align-items-center text-bg-success border-0" role="alert"
            aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="liveToastDeleteSuccessBody"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                    aria-label="Close"></button>
            </div>
        </div>
        <div id="liveToastDeleteError" class="toast align-items-center text-bg-danger border-0" role="alert"
            aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="liveToastDeleteErrorBody"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                    aria-label="Close"></button>
            </div>
        </div>
    </div>

    {{-- Modal Form Input Point --}}
    <div class="modal fade" tabindex="-1" id="modalInputPoint" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Input Point</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('points.store') }}" method="post" enctype="multipart/form-data">
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
                        <div class="mb-3">
                            <label for="image-point" class="form-label">Image</label>
                            <input class="form-control" type="file" id="image-point" name="image" onchange="document.getElementById('preview-image-point').src = window.URL.createObjectURL(this.files[0])">
                            <div class="mb-3">
                                <img src="" alt="" id="preview-image-point" class="img-thumbnail" width="400"></div>
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
                <form action="{{ route('polylines.store') }}" method="post" enctype="multipart/form-data">
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
                        <div class="mb-3">
                            <label for="image-polyline" class="form-label">Image</label>
                            <input class="form-control" type="file" id="image-polyline" name="image" onchange="document.getElementById('preview-image-polyline').src = window.URL.createObjectURL(this.files[0])">
                            <div class="mb-3">
                                <img src="" alt="" id="preview-image-polyline" class="img-thumbnail" width="400"></div>
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
                <form action="{{ route('polygons.store') }}" method="post" enctype="multipart/form-data">
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
                        <div class="mb-3">
                            <label for="image-polygon" class="form-label">Image</label>
                            <input class="form-control" type="file" id="image-polygon" name="image" onchange="document.getElementById('preview-image-polygon').src = window.URL.createObjectURL(this.files[0])">
                            <div class="mb-3">
                                <img src="" alt="" id="preview-image-polygon" class="img-thumbnail" width="400"></div>
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
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });

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
                // Add to polylines layer
                polylinesLayer.addLayer(layer);

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
                // Add to polygons layer
                polygonsLayer.addLayer(layer);

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
                // Add to points layer
                pointsLayer.addLayer(layer);

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

        // Create layer groups for control layers
        var pointsLayer = L.layerGroup().addTo(map);
        var polylinesLayer = L.layerGroup().addTo(map);
        var polygonsLayer = L.layerGroup().addTo(map);

        var storageImageUrl = "{{ asset('storage/images') }}";
        var csrfToken = '{{ csrf_token() }}';
        var deleteBaseUrls = {
            point: "{{ url('delete-point') }}",
            polyline: "{{ url('delete-polyline') }}",
            polygon: "{{ url('delete-polygon') }}"
        };

        function deleteUrl(type, id) {
            return deleteBaseUrls[type] + '/' + id;
        }

        function showToast(elementId, bodyId, message) {
            var toastEl = document.getElementById(elementId);
            if (!toastEl) {
                return;
            }
            var bodyEl = document.getElementById(bodyId);
            if (bodyEl) {
                bodyEl.textContent = message;
            }
            var toast = new bootstrap.Toast(toastEl);
            toast.show();
        }

        function handleDeleteResponse(response, layer) {
            if (!response.ok) {
                return response.json().then(function(data) {
                    throw new Error(data.message || 'Gagal menghapus data.');
                });
            }
            return response.json();
        }

        map.on('popupopen', function(e) {
            var sourceLayer = e.popup._source;
            var popupEl = e.popup.getElement();
            if (!popupEl) {
                return;
            }
            var deleteButton = popupEl.querySelector('.delete-feature-btn');
            if (!deleteButton) {
                return;
            }
            deleteButton.addEventListener('click', function(ev) {
                ev.preventDefault();
                ev.stopPropagation();

                if (!confirm('Yakin ingin dihapus?')) {
                    return;
                }

                var type = this.dataset.type;
                var id = this.dataset.id;
                if (!type || !id) {
                    return;
                }

                fetch(deleteUrl(type, id), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                })
                .then(function(response) {
                    return handleDeleteResponse(response, sourceLayer);
                })
                .then(function(data) {
                    if (sourceLayer) {
                        map.removeLayer(sourceLayer);
                    }
                    map.closePopup();
                    showToast('liveToastDeleteSuccess', 'liveToastDeleteSuccessBody', data.message || 'Data berhasil dihapus.');
                })
                .catch(function(err) {
                    showToast('liveToastDeleteError', 'liveToastDeleteErrorBody', err.message || 'Gagal menghapus data.');
                });
            });
        });

        // Load saved points
        $.getJSON("{{ route('geojson.points') }}", function(data) {
            L.geoJSON(data, {
                pointToLayer: function(feature, latlng) {
                    return L.marker(latlng, { icon: pointIcon });
                },
                onEachFeature: function(feature, layer) {
                    var popupContent = '<b>Nama:</b> ' + (feature.properties.name || '') + '<br>' +
                        '<b>Deskripsi:</b> ' + (feature.properties.description || '') + '<br>' +
                        '<b>Dibuat:</b> ' + (feature.properties.created_at || '');

                    if (feature.properties.image) {
                        var imageUrl = feature.properties.image;
                        if (!imageUrl.startsWith('http') && !imageUrl.startsWith('/')) {
                            imageUrl = storageImageUrl + '/' + imageUrl;
                        }
                        popupContent += '<br><img src="' + imageUrl + '" alt="Point image" style="max-width: 150px; margin-top: 5px; display: block;">';
                    }
                    popupContent += '<br><button type="button" class="btn btn-sm btn-danger delete-feature-btn" data-type="point" data-id="' + feature.properties.id + '"><i class="fa-solid fa-trash"></i></button>';
                    layer.bindPopup(popupContent);
                }
            }).eachLayer(function(layer) {
                pointsLayer.addLayer(layer);
            });
        });

        // Load saved polylines
        $.getJSON("{{ route('geojson.polylines') }}", function(data) {
            L.geoJSON(data, {
                style: {
                    color: '#0066ff',
                    weight: 3,
                    opacity: 0.8
                },
                onEachFeature: function(feature, layer) {
                    var popupContent = '<b>Nama:</b> ' + (feature.properties.name || '') + '<br>' +
                        '<b>Deskripsi:</b> ' + (feature.properties.description || '') + '<br>' +
                        '<b>Dibuat:</b> ' + (feature.properties.created_at || '');
                    if (feature.properties.image) {
                        var imageUrl = feature.properties.image;
                        if (!imageUrl.startsWith('http') && !imageUrl.startsWith('/')) {
                            imageUrl = storageImageUrl + '/' + imageUrl;
                        }
                        popupContent += '<br><img src="' + imageUrl + '" alt="Polyline image" style="max-width: 150px; margin-top: 5px; display: block;">';
                    }
                    popupContent += '<br><button type="button" class="btn btn-sm btn-danger delete-feature-btn" data-type="polyline" data-id="' + feature.properties.id + '"><i class="fa-solid fa-trash"></i></button>';
                    layer.bindPopup(popupContent);
                }
            }).eachLayer(function(layer) {
                polylinesLayer.addLayer(layer);
            });
        });

        // Load saved polygons
        $.getJSON("{{ route('geojson.polygons') }}", function(data) {
            L.geoJSON(data, {
                style: {
                    color: '#0066ff',
                    weight: 2,
                    opacity: 0.8,
                    fillColor: '#99ccff',
                    fillOpacity: 0.3
                },
                onEachFeature: function(feature, layer) {
                    var popupContent = '<b>Nama:</b> ' + (feature.properties.name || '') + '<br>' +
                        '<b>Deskripsi:</b> ' + (feature.properties.description || '') + '<br>' +
                        '<b>Dibuat:</b> ' + (feature.properties.created_at || '');
                    if (feature.properties.image) {
                        var imageUrl = feature.properties.image;
                        if (!imageUrl.startsWith('http') && !imageUrl.startsWith('/')) {
                            imageUrl = storageImageUrl + '/' + imageUrl;
                        }
                        popupContent += '<br><img src="' + imageUrl + '" alt="Polygon image" style="max-width: 150px; margin-top: 5px; display: block;">';
                    }
                    popupContent += '<br><button type="button" class="btn btn-sm btn-danger delete-feature-btn" data-type="polygon" data-id="' + feature.properties.id + '"><i class="fa-solid fa-trash"></i></button>';
                    layer.bindPopup(popupContent);
                }
            }).eachLayer(function(layer) {
                polygonsLayer.addLayer(layer);
            });
        });

        // Control Layer
        var baseMaps = {};

        var overlayMaps = {
            "Points": pointsLayer,
            "Polylines": polylinesLayer,
            "Polygons": polygonsLayer
        };

        var layerControl = L.control.layers(baseMaps, overlayMaps).addTo(map);
    </script>
@endsection
