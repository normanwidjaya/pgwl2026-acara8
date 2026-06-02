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
            width: min(360px, calc(100% - 32px));
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
    <div class="edit-panel">
        <h5>Edit Polyline</h5>
        <form id="formEditPolyline" action="{{ route('polylines.update', $polyline->id) }}" method="post" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="edit_name_polyline" class="form-label">Name</label>
                <input type="text" class="form-control" id="edit_name_polyline" name="name" value="{{ $polyline->name }}" required>
            </div>
            <div class="mb-3">
                <label for="edit_description_polyline" class="form-label">Description</label>
                <textarea class="form-control" id="edit_description_polyline" name="description" rows="3" required>{{ $polyline->description }}</textarea>
            </div>
            <div class="mb-3">
                <label for="edit_geometry_polyline" class="form-label">Geometry</label>
                <textarea class="form-control" id="edit_geometry_polyline" name="geometry_polyline" rows="3" readonly required>{{ $polyline->geom }}</textarea>
                <small class="form-text text-muted">Polyline akan tersimpan saat Anda mengubah bentuk di peta.</small>
            </div>
            <div class="mb-3">
                <label for="edit_image_polyline" class="form-label">Image</label>
                <input class="form-control" type="file" id="edit_image_polyline" name="image" accept="image/*" onchange="previewImage(this, 'edit_preview_image_polyline')">
                <div class="mt-2">
                    <img src="{{ $polyline->image ? asset('storage/images/' . $polyline->image) : '' }}" alt="" id="edit_preview_image_polyline" class="img-thumbnail" width="200" style="display: {{ $polyline->image ? 'block' : 'none' }};">
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

    <div class="modal fade" id="successModalPolyline" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Polyline Berhasil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Perubahan polyline berhasil disimpan.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Tutup</button>
                </div>
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

        var editingPolylineId = {{ $polyline->id }};

        function updateGeometryFromLayer(layer) {
            if (!layer) return;
            var geojson = layer.toGeoJSON();
            var wkt = Terraformer.geojsonToWKT(geojson.geometry);
            $('#edit_geometry_polyline').val(wkt);
        }

        function ensurePolylineGeometry() {
            if (drawnItems.getLayers().length > 0) {
                updateGeometryFromLayer(drawnItems.getLayers()[0]);
            }
        }

        map.on('draw:edited', function(e) {
            e.layers.eachLayer(function(layer) {
                updateGeometryFromLayer(layer);
            });
        });

        $.getJSON("{{ route('geojson.polylines') }}", function(data) {
            L.geoJSON(data, {
                filter: function(feature) {
                    return feature.properties.id == editingPolylineId;
                },
                style: {
                    color: '#0066ff',
                    weight: 3,
                    opacity: 0.8
                },
                onEachFeature: function(feature, layer) {
                    drawnItems.addLayer(layer);
                    updateGeometryFromLayer(layer);
                }
            }).eachLayer(function(layer) {
                if (drawnItems.getBounds().isValid()) {
                    map.fitBounds(drawnItems.getBounds(), { padding: [20, 20] });
                }
            });
            $('.edit-panel').show();
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

        function previewImage(input, previewId) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var preview = document.getElementById(previewId);
                    if (preview) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    }
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        $('#formEditPolyline').on('submit', function(e) {
            e.preventDefault();
            ensurePolylineGeometry();
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
                        throw new Error(data.message || 'Failed to update polyline.');
                    });
                }
                return response.json();
            })
            .then(function(data) {
                showToast('liveToastEditSuccess', 'liveToastEditSuccessBody', data.message || 'Polyline updated successfully.');
                var successModal = new bootstrap.Modal(document.getElementById('successModalPolyline'));
                successModal.show();
                setTimeout(function() {
                    successModal.hide();
                    window.location.href = '{{ route('peta') }}';
                }, 2000);
            })
            .catch(function(err) {
                showToast('liveToastEditError', 'liveToastEditErrorBody', err.message || 'Failed to update polyline.');
            });
        });
    </script>
@endsection
