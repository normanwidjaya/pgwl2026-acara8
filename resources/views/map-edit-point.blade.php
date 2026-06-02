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

    {{-- Toast Notifications --}}
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

    {{-- Modal Form Edit Point --}}
    <div class="modal fade" tabindex="-1" id="modalEditPoint" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Point</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formEditPoint" action="{{ route('point.update', $id) }}" method="post" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_name_point" class="form-label">Name</label>
                            <input type="text" class="form-control" id="edit_name_point" name="name" placeholder="Fill name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_description_point" class="form-label">Description</label>
                            <textarea class="form-control" id="edit_description_point" name="description" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="edit_geometry_point" class="form-label">Geometry</label>
                            <textarea class="form-control" id="edit_geometry_point" name="geometry_point" rows="3" readonly required></textarea>
                            <small class="form-text text-muted">Geometry will be updated when you move the marker on the map.</small>
                        </div>
                        <div class="mb-3">
                            <label for="edit_image_point" class="form-label">Image</label>
                            <input class="form-control" type="file" id="edit_image_point" name="image" accept="image/*" onchange="document.getElementById('edit_preview_image_point').src = window.URL.createObjectURL(this.files[0])">
                            <div class="mb-3">
                                <img src="" alt="" id="edit_preview_image_point" class="img-thumbnail" width="400" style="display: none;">
                            </div>
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
        // Initialize map and set view to Yogyakarta coordinates with zoom level
        var map = L.map('map').setView([-7.7956, 110.3695], 13);

        // Add OpenStreetMap tile layer
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

        // Create layer group for points
        var pointsLayer = L.layerGroup().addTo(map);

        // Variables for editing
        var currentEditingMarker = null;
        var currentEditingId = null;

        // URLs and tokens
        var storageImageUrl = "{{ asset('storage/images') }}";
        var csrfToken = '{{ csrf_token() }}';

        // Toast notification function
        function showToast(elementId, bodyId, message) {
            var toastEl = document.getElementById(elementId);
            if (!toastEl) return;
            var bodyEl = document.getElementById(bodyId);
            if (bodyEl) bodyEl.textContent = message;
            var toast = new bootstrap.Toast(toastEl);
            toast.show();
        }

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
                    popupContent += '<br><button type="button" class="btn btn-sm btn-warning edit-feature-btn" data-type="point" data-id="' + feature.properties.id + '"><i class="fa-solid fa-pen-to-square"></i> Edit</button>';
                    layer.bindPopup(popupContent);
                }
            }).eachLayer(function(layer) {
                pointsLayer.addLayer(layer);
            });
        });

        // Handle popup open for edit button
        map.on('popupopen', function(e) {
            var editButton = e.popup.getElement().querySelector('.edit-feature-btn');
            if (editButton) {
                editButton.addEventListener('click', function(ev) {
                    ev.preventDefault();
                    ev.stopPropagation();

                    var id = this.dataset.id;
                    if (!id) return;

                    // Fetch point data
                    fetch('/points/' + id, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json'
                        }
                    })
                    .then(function(response) {
                        if (!response.ok) throw new Error('Failed to fetch point data.');
                        return response.json();
                    })
                    .then(function(data) {
                        // Fill modal with data
                        $('#edit_name_point').val(data.name);
                        $('#edit_description_point').val(data.description);
                        $('#edit_geometry_point').val(data.geom);

                        if (data.image) {
                            $('#edit_preview_image_point').attr('src', storageImageUrl + '/' + data.image).show();
                        } else {
                            $('#edit_preview_image_point').attr('src', '').hide();
                        }

                        $('#formEditPoint').attr('action', '/points/' + id);

                        // Show modal
                        var modalEl = document.getElementById('modalEditPoint');
                        var bsModal = new bootstrap.Modal(modalEl);
                        bsModal.show();

                        // Reload page when modal is hidden
                        $('#modalEditPoint').on('hidden.bs.modal', function() {
                            location.reload();
                        });
                    })
                    .catch(function(err) {
                        showToast('liveToastEditError', 'liveToastEditErrorBody', err.message || 'Failed to load point data.');
                    });
                });
            }
        });

        // Handle edit form submission
        $('#formEditPoint').on('submit', function(e) {
            e.preventDefault();
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
                $('#modalEditPoint').modal('hide');
                location.reload();
                showToast('liveToastEditSuccess', 'liveToastEditSuccessBody', data.message || 'Point updated successfully.');
            })
            .catch(function(err) {
                showToast('liveToastEditError', 'liveToastEditErrorBody', err.message || 'Failed to update point.');
            });
        });

        // Layer control
        var baseMaps = {};
        var overlayMaps = {
            "Points": pointsLayer
        };
        var layerControl = L.control.layers(baseMaps, overlayMaps).addTo(map);
    </script>
@endsection
