@extends('layouts.template')

@section('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
        integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        html,
        body {
            min-height: 100%;
            margin: 0;
            padding: 0;
        }

        #map {
            position: absolute;
            top: 64px;
            bottom: 0;
            left: 0;
            right: 0;
            width: 100%;
            height: calc(100vh - 64px);
            z-index: 1;
        }

        .edit-panel {
            display: none;
            position: fixed;
            top: 84px;
            left: 50%;
            transform: translateX(-50%);
            width: min(340px, calc(100% - 32px));
            background: rgba(255, 255, 255, 0.98);
            border-radius: 12px;
            padding: 18px;
            box-shadow: 0 16px 32px rgba(0, 0, 0, 0.14);
            z-index: 2000;
            max-height: calc(100vh - 120px);
            overflow-y: auto;
        }

        .edit-panel h5 {
            margin-bottom: 16px;
            font-weight: 600;
        }

        .form-text {
            font-size: 0.85rem;
        }

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

        .leaflet-marker-draggable {
            cursor: move !important;
        }
    </style>
@endsection

@section('content')
    <div class="edit-panel">
        <h5>Edit Point</h5>
        <form id="formEditPoint" action="{{ route('points.update', $point->id) }}" method="post" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="edit_name_point" class="form-label">Name</label>
                <input type="text" class="form-control" id="edit_name_point" name="name" value="{{ $point->name }}" required>
            </div>
            <div class="mb-3">
                <label for="edit_description_point" class="form-label">Description</label>
                <textarea class="form-control" id="edit_description_point" name="description" rows="3" required>{{ $point->description }}</textarea>
            </div>
            <div class="mb-3">
                <label for="edit_geometry_point" class="form-label">Geometry</label>
                <textarea class="form-control" id="edit_geometry_point" name="geometry_point" rows="3" readonly required>{{ $point->geom }}</textarea>
                <small class="form-text text-muted">Posisi marker akan tersimpan saat klik Save.</small>
            </div>
            <div class="mb-3">
                <label for="edit_image_point" class="form-label">Image</label>
                <input class="form-control" type="file" id="edit_image_point" name="image" accept="image/*" onchange="previewImage(this)">
                <div class="mt-2">
                    <img src="{{ $point->image ? asset('storage/images/' . $point->image) : '' }}" alt="" id="edit_preview_image_point" class="img-thumbnail" width="200" style="display: {{ $point->image ? 'block' : 'none' }};">
                </div>
            </div>
            <div class="modal-footer justify-content-end gap-2">
                <a href="{{ route('peta') }}" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>

    <div id="map"></div>

    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1050; margin-bottom: 20px;">
        <div id="liveToastEditSuccess" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="liveToastEditSuccessBody"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
        <div id="liveToastEditError" class="toast align-items-center text-bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="liveToastEditErrorBody"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>
    <script src="https://unpkg.com/@terraformer/wkt"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        var map = L.map('map').setView([-7.7956, 110.3695], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        const pointIcon = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });

        var drawnItems = new L.FeatureGroup();
        map.addLayer(drawnItems);

        var drawControl = new L.Control.Draw({
            draw: false,
            edit: {
                featureGroup: drawnItems,
                edit: true,
                remove: false
            }
        });
        map.addControl(drawControl);

        var editingPointId = {{ $point->id }};
        var editableMarker = null;

        function updateGeometryFromLayer(layer) {
            var geojson = layer.toGeoJSON();
            var wkt = Terraformer.geojsonToWKT(geojson.geometry);
            $('#edit_geometry_point').val(wkt);
        }

        function updateGeometryFromMarker(marker) {
            if (!marker) return;
            var geojson = marker.toGeoJSON();
            var wkt = Terraformer.geojsonToWKT(geojson.geometry);
            $('#edit_geometry_point').val(wkt);
        }

        map.on('draw:edited', function(e) {
            e.layers.eachLayer(function(layer) {
                updateGeometryFromLayer(layer);
            });
            $('.edit-panel').show();
        });

        map.on('draw:editstart', function(e) {
            if (editableMarker && editableMarker.dragging) {
                editableMarker.dragging.enable();
            }
        });

        map.on('draw:editstop', function(e) {
            if (editableMarker && editableMarker.dragging) {
                editableMarker.dragging.disable();
            }
        });

        var storageImageUrl = "{{ asset('storage/images') }}";

        $.getJSON("{{ route('geojson.points') }}", function(data) {
            L.geoJSON(data, {
                pointToLayer: function(feature, latlng) {
                    var marker = L.marker(latlng, {
                        icon: pointIcon,
                        draggable: false
                    });

                    if (feature.properties.id == editingPointId) {
                        editableMarker = marker;
                        drawnItems.addLayer(marker);
                        marker.dragging.disable();
                        marker.on('dragend', function(e) {
                            updateGeometryFromMarker(e.target);
                        });
                    }

                    return marker;
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

                    layer.bindPopup(popupContent);
                }
            }).addTo(map);
        });

        var csrfToken = '{{ csrf_token() }}';

        function showToast(elementId, bodyId, message) {
            var toastEl = document.getElementById(elementId);
            if (!toastEl) return;
            var bodyEl = document.getElementById(bodyId);
            if (bodyEl) bodyEl.textContent = message;
            var toast = new bootstrap.Toast(toastEl);
            toast.show();
        }

        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#edit_preview_image_point').attr('src', e.target.result).show();
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function ensureMarkerGeometry() {
            if (editableMarker) {
                updateGeometryFromMarker(editableMarker);
            } else if (drawnItems.getLayers().length > 0) {
                updateGeometryFromLayer(drawnItems.getLayers()[0]);
            }
        }

        $('#formEditPoint').on('submit', function(e) {
            e.preventDefault();
            ensureMarkerGeometry();
            var formData = new FormData(this);

            fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(function(response) {
                if (!response.ok) {
                    return response.json().then(function(data) {
                        throw new Error(data.message || 'Failed to update point.');
                    });
                }
                return response.json();
            })
            .then(function(data) {
                showToast('liveToastEditSuccess', 'liveToastEditSuccessBody', data.message || 'Point updated successfully.');
                setTimeout(function() {
                    window.location.href = '{{ route("peta") }}';
                }, 2000);
            })
            .catch(function(err) {
                showToast('liveToastEditError', 'liveToastEditErrorBody', err.message || 'Failed to update point.');
            });
        });
    </script>
@endsection
