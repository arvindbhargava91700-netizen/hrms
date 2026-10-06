<div class="container-fluid py-3">
    @if (session()->has('success'))
        <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show rounded-3 mb-4">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger border-0 shadow-sm alert-dismissible fade show rounded-3 mb-4">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif


    <!-- Live Geofence & Location Verification Status Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 {{ $working_mode === 'office' ? ($isWithinGeofence ? 'bg-success bg-opacity-10 border-success' : 'bg-danger bg-opacity-10 border-danger') : 'bg-primary bg-opacity-10 border-primary' }}" style="border-left: 6px solid {{ $working_mode === 'office' ? ($isWithinGeofence ? '#198754' : '#dc3545') : '#0d6efd' }} !important;">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3.5">
                    <div class="rounded-circle p-3 d-flex align-items-center justify-content-center text-white shadow-sm flex-shrink-0" style="width: 52px; height: 52px; background: {{ $working_mode === 'office' ? ($isWithinGeofence ? '#198754' : '#dc3545') : '#0d6efd' }};">
                        @if($working_mode === 'office')
                            <i class="bi {{ $isWithinGeofence ? 'bi-geo-alt-fill' : 'bi-exclamation-triangle-fill' }} fs-3"></i>
                        @elseif($working_mode === 'remote')
                            <i class="bi bi-house-door-fill fs-3"></i>
                        @else
                            <i class="bi bi-briefcase-fill fs-3"></i>
                        @endif
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2 flex-wrap" style="font-size:1rem;">
                            <span>Working Mode: <strong>{{ ucfirst($working_mode) }} Work</strong></span>
                            @if($working_mode === 'office')
                                @if($isWithinGeofence)
                                    <span class="badge bg-success rounded-pill px-3 py-1.5 fs-7"><i class="bi bi-check-circle-fill me-1"></i>Inside Office Geofence Range</span>
                                @else
                                    <span class="badge bg-danger rounded-pill px-3 py-1.5 fs-7"><i class="bi bi-x-circle-fill me-1"></i>Out of Geofence Range</span>
                                @endif
                            @else
                                <span class="badge bg-primary rounded-pill px-3 py-1.5 fs-7"><i class="bi bi-shield-check me-1"></i>Geofence Exempt</span>
                            @endif
                        </h6>
                        <p class="mb-0 text-secondary small">
                            @if($working_mode === 'office')
                                @if(!empty($officeLat) && !empty($officeLng))
                                    @if(!is_null($distanceMeters))
                                        Office Allowed Radius: <strong>{{ $officeRadius }}m</strong> | Your Distance to Office: <strong class="{{ $isWithinGeofence ? 'text-success' : 'text-danger' }} fs-6">{{ $distanceMeters }} meters</strong>
                                        @if(!$isWithinGeofence)
                                            <span class="text-danger fw-bold d-block mt-1"><i class="bi bi-slash-circle me-1"></i> Attendance punch is blocked because you are outside the {{ $officeRadius }}m office range limit!</span>
                                        @else
                                            <span class="text-success fw-bold d-block mt-1"><i class="bi bi-check-circle me-1"></i> Location verified! You are inside the office range. Attendance allowed.</span>
                                        @endif
                                    @else
                                        Office Allowed Radius: <strong>{{ $officeRadius }}m</strong> | <span class="text-muted"><i class="bi bi-clock me-1"></i>Fetching live GPS distance...</span>
                                    @endif
                                @else
                                    <span class="text-muted"><i class="bi bi-info-circle me-1"></i>Office GPS coordinates not configured in HRMS Settings. Geofence check is temporarily bypassed.</span>
                                @endif
                            @elseif($working_mode === 'remote')
                                <i class="bi bi-info-circle me-1 text-primary"></i>Selfie + GPS Lat/Lon will be logged for Remote/WFH record. Range restriction is exempt.
                            @else
                                <i class="bi bi-info-circle me-1 text-primary"></i>Selfie + GPS Lat/Lon will be logged for Field Work location tracking. Range restriction is exempt.
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-0 bg-white rounded-4 overflow-hidden h-100">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-camera me-2 text-primary"></i>Capture Verification</h5>
                </div>
                <div class="card-body">
                    @if($isHoliday)
                        <div class="alert alert-info border-0 shadow-sm text-center py-5">
                            <i class="bi bi-calendar-event text-info display-4 mb-3 d-block"></i>
                            <h4 class="fw-bold">Today is a Holiday</h4>
                            <p class="text-muted mb-0">It's <strong>{{ $holidayName }}</strong> today! Enjoy your day off.</p>
                        </div>
                    @elseif($isWeekOff)
                        <div class="alert alert-secondary border-0 shadow-sm text-center py-5">
                            <i class="bi bi-cup-hot text-secondary display-4 mb-3 d-block"></i>
                            <h4 class="fw-bold">Today is a Week Off</h4>
                            <p class="text-muted mb-0">Enjoy your weekend!</p>
                        </div>
                    @elseif($isFullLeave)
                        <div class="alert alert-warning border-0 shadow-sm text-center py-5">
                            <i class="bi bi-umbrella text-warning display-4 mb-3 d-block"></i>
                            <h4 class="fw-bold">You are on Leave</h4>
                            <p class="text-muted mb-0">You have an approved full-day leave for today. Have a great day!</p>
                        </div>
                    @elseif($isAbsent)
                        <div class="alert alert-danger border-0 shadow-sm text-center py-5">
                            <i class="bi bi-x-circle text-danger display-4 mb-3 d-block"></i>
                            <h4 class="fw-bold">You are Absent</h4>
                            <p class="text-muted mb-0">You have been marked absent for today.</p>
                        </div>
                    @endif
                    
                    @if($isHalfLeave)
                        <div class="alert alert-primary border-0 shadow-sm text-center py-3 mb-4">
                            <h6 class="fw-bold mb-1"><i class="bi bi-clock-half me-2"></i>Half-Day Leave</h6>
                            <p class="text-muted mb-0 small">You are on a half-day leave. Please log your partial hours.</p>
                        </div>
                    @endif
                    
                    <div style="display: {{ ($isHoliday || $isWeekOff || $isFullLeave || $isAbsent) ? 'none' : 'block' }}">
                        <div class="camera-container text-center mb-3" wire:ignore>
                            <video id="cameraStream" autoplay playsinline muted style="width: 100%; max-width: 400px; border-radius: 12px; background: #000;"></video>
                        <canvas id="cameraCanvas" style="display: none;"></canvas>
                        <img id="photoPreview" style="display: none; width: 100%; max-width: 400px; border-radius: 12px; border: 2px solid #28a745;" />
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-center mb-3" wire:ignore>
                        <button type="button" id="startCameraBtn" class="btn btn-outline-primary" onclick="startCamera()">
                            <i class="bi bi-camera-video me-1"></i> Start Camera
                        </button>
                        <button type="button" id="captureBtn" class="btn btn-primary" onclick="capturePhoto()" style="display: none;">
                            <i class="bi bi-camera me-1"></i> Capture Selfie
                        </button>
                        <button type="button" id="retakeBtn" class="btn btn-outline-secondary" onclick="retakePhoto()" style="display: none;">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Retake
                        </button>
                    </div>
                    
                    <div class="location-status text-center mt-3" wire:ignore>
                        <div id="mapContainer" style="width: 100%; height: 150px; border-radius: 12px; display: none; margin-bottom: 10px;"></div>
                        <p id="gpsStatus" class="text-muted mb-1"><i class="bi bi-geo-alt me-1"></i> Location pending...</p>
                    </div>

                    @if((!$todayAttendance || !$todayAttendance->check_out) && count($checklists) > 0)
                        <div class="mt-4 pt-3 border-top">
                            <h6 class="fw-bold mb-3"><i class="bi bi-card-checklist me-2 text-primary"></i>{{ (!$todayAttendance || !$todayAttendance->check_in) ? 'Pre-Check-in Checklist' : 'Pre-Check-out Checklist' }}</h6>
                            <div class="alert alert-light border shadow-sm">
                                @foreach($checklists as $checklist)
                                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                                        <div class="fw-600 text-dark">
                                            {{ $checklist->question }}
                                        </div>
                                        <div class="d-flex gap-2">
                                            <input type="radio" class="btn-check" wire:model="checklistAnswers.{{ $checklist->id }}" name="chk_{{ $checklist->id }}" id="chk_yes_{{ $checklist->id }}" value="1" autocomplete="off">
                                            <label class="btn btn-outline-success btn-sm px-3" for="chk_yes_{{ $checklist->id }}">Yes</label>

                                            <input type="radio" class="btn-check" wire:model="checklistAnswers.{{ $checklist->id }}" name="chk_{{ $checklist->id }}" id="chk_no_{{ $checklist->id }}" value="0" autocomplete="off">
                                            <label class="btn btn-outline-danger btn-sm px-3" for="chk_no_{{ $checklist->id }}">No</label>
                                        </div>
                                    </div>
                                    @error('checklistAnswers.'.$checklist->id) <div class="text-danger small mt-n2 mb-2">{{ $message }}</div> @enderror
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="d-grid gap-2 d-md-block text-center mt-4">
                        @if($todayAttendance && $todayAttendance->check_in && !$todayAttendance->check_out)
                            <button type="button" id="submitBtn" wire:click="processCheckInOut" class="btn btn-danger btn-lg px-md-5 shadow-sm" disabled wire:loading.attr="disabled" @if($working_mode === 'office' && !$isWithinGeofence && !empty($officeLat) && !empty($officeLng)) data-geofence-blocked="true" @endif>
                                <i class="bi bi-box-arrow-right me-2"></i> Check Out
                            </button>
                        @elseif(!$todayAttendance || !$todayAttendance->check_in)
                            <button type="button" id="submitBtn" wire:click="processCheckInOut" class="btn btn-success btn-lg px-md-5 shadow-sm" disabled wire:loading.attr="disabled" @if($working_mode === 'office' && !$isWithinGeofence && !empty($officeLat) && !empty($officeLng)) data-geofence-blocked="true" @endif>
                                <i class="bi bi-box-arrow-in-right me-2"></i> Check In
                            </button>
                        @else
                            <div class="alert alert-info border-0 shadow-sm">
                                <i class="bi bi-info-circle me-2"></i> You have already checked out for today.
                            </div>
                        @endif
                        
                        <div wire:loading wire:target="processCheckInOut" class="mt-2 text-primary">
                            <span class="spinner-border spinner-border-sm" role="status"></span> Processing...
                        </div>
                    </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-0 bg-white rounded-4 overflow-hidden h-100">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-clock-history me-2 text-primary"></i>Today's Status</h5>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-4 p-3 bg-light rounded-3">
                        <div class="display-4 me-3 text-primary"><i class="bi bi-calendar2-day"></i></div>
                        <div>
                            <h4 class="mb-0">{{ now()->format('l, F j, Y') }}</h4>
                            <p class="text-muted mb-0">Current Date</p>
                        </div>
                    </div>

                    @if($todayAttendance)
                    <div class="mb-4 p-3 border rounded-3 bg-white text-center shadow-sm">
                        <h6 class="text-uppercase fw-bold text-muted mb-2">Current Status</h6>
                        <div class="d-flex justify-content-center align-items-center gap-2 flex-wrap mb-1">
                            @if($todayAttendance->status === 'punch_out')
                                <span class="badge bg-success rounded-pill px-4 py-2 fs-6">Punch Out</span>
                            @elseif($todayAttendance->status === 'absent')
                                <span class="badge bg-danger rounded-pill px-4 py-2 fs-6">Absent</span>
                            @elseif($todayAttendance->status === 'half_day')
                                <span class="badge bg-warning text-dark rounded-pill px-4 py-2 fs-6">Half Day</span>
                            @elseif($todayAttendance->status === 'short_leave')
                                <span class="badge bg-info rounded-pill px-4 py-2 fs-6">Short Leave</span>
                            @elseif($todayAttendance->status === 'punch_in')
                                <span class="badge bg-primary rounded-pill px-4 py-2 fs-6">Punch In</span>
                            @else
                                <span class="badge bg-secondary rounded-pill px-4 py-2 fs-6">{{ ucfirst($todayAttendance->status) }}</span>
                            @endif

                            <span class="badge bg-dark bg-gradient rounded-pill px-3 py-2 fs-6">
                                <i class="bi bi-laptop me-1"></i>{{ ucfirst($todayAttendance->working_mode ?: 'office') }} Mode
                            </span>
                        </div>
                        @if($todayAttendance->late_minutes > 0)
                            <div class="mt-2">
                                <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3 py-2">
                                    <i class="bi bi-clock-history me-1"></i>Late by {{ $todayAttendance->late_minutes }} mins
                                </span>
                            </div>
                        @endif
                    </div>
                    @endif
                    
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="p-3 border rounded-3 position-relative overflow-hidden h-100 {{ ($todayAttendance && $todayAttendance->check_in) ? 'bg-success bg-opacity-10 border-success' : 'bg-light' }}">
                                <p class="text-muted small text-uppercase fw-bold mb-1">Check In Time</p>
                                <h3 class="mb-0 {{ ($todayAttendance && $todayAttendance->check_in) ? 'text-success' : 'text-dark' }}">
                                    {{ ($todayAttendance && $todayAttendance->check_in) ? \Carbon\Carbon::parse($todayAttendance->check_in)->format('h:i A') : '--:-- --' }}
                                </h3>
                                @if($todayAttendance && $todayAttendance->check_in_selfie)
                                    <div class="mt-2">
                                        <img src="{{ $todayAttendance->check_in_selfie }}" alt="Check In Selfie" class="img-thumbnail" style="height: 60px; object-fit: cover; border-radius: 8px;">
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="p-3 border rounded-3 position-relative overflow-hidden h-100 {{ ($todayAttendance && $todayAttendance->check_out) ? 'bg-danger bg-opacity-10 border-danger' : 'bg-light' }}">
                                <p class="text-muted small text-uppercase fw-bold mb-1">Check Out Time</p>
                                <h3 class="mb-0 {{ ($todayAttendance && $todayAttendance->check_out) ? 'text-danger' : 'text-dark' }}">
                                    {{ ($todayAttendance && $todayAttendance->check_out) ? \Carbon\Carbon::parse($todayAttendance->check_out)->format('h:i A') : '--:-- --' }}
                                </h3>
                                @if($todayAttendance && $todayAttendance->check_out_selfie)
                                    <div class="mt-2">
                                        <img src="{{ $todayAttendance->check_out_selfie }}" alt="Check Out Selfie" class="img-thumbnail" style="height: 60px; object-fit: cover; border-radius: 8px;">
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    
    <script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&libraries=places"></script>
    <script>
        let video = document.getElementById('cameraStream');
        let canvas = document.getElementById('cameraCanvas');
        let photoPreview = document.getElementById('photoPreview');
        let stream = null;
        
        let hasPhoto = false;
        let hasLocation = false;
        
        // On mount, ask for GPS
        document.addEventListener('livewire:initialized', () => {
            getGPSLocation();
            
            Livewire.hook('commit', ({ component, commit, respond, succeed, fail }) => {
                succeed(({ snapshot, effect }) => {
                    setTimeout(() => {
                        checkSubmitReady();
                    }, 50);
                });
            });
        });

        function getGPSLocation() {
            let gpsStatus = document.getElementById('gpsStatus');
            if (navigator.geolocation) {
                gpsStatus.innerHTML = '<span class="spinner-border spinner-border-sm text-primary" role="status"></span> Fetching GPS location...';
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        let lat = position.coords.latitude;
                        let lng = position.coords.longitude;
                        @this.set('latitude', lat, false);
                        @this.set('longitude', lng, false);
                        @this.call('checkGeofenceStatus', lat, lng);
                        
                        hasLocation = true;
                        
                        // Use Google Maps Geocoder
                        const geocoder = new google.maps.Geocoder();
                        const latlng = { lat: lat, lng: lng };
                        
                        geocoder.geocode({ location: latlng }, (results, status) => {
                            if (status === "OK") {
                                if (results[0]) {
                                    gpsStatus.innerHTML = `<i class="bi bi-geo-alt-fill text-success"></i> ${results[0].formatted_address}`;
                                } else {
                                    gpsStatus.innerHTML = `<i class="bi bi-geo-alt-fill text-success"></i> Location locked: ${lat.toFixed(4)}, ${lng.toFixed(4)}`;
                                }
                            } else {
                                gpsStatus.innerHTML = `<i class="bi bi-geo-alt-fill text-success"></i> Location locked: ${lat.toFixed(4)}, ${lng.toFixed(4)}`;
                            }
                        });

                        // Show map
                        let mapContainer = document.getElementById('mapContainer');
                        mapContainer.style.display = 'block';
                        const map = new google.maps.Map(mapContainer, {
                            zoom: 15,
                            center: latlng,
                            disableDefaultUI: true,
                        });
                        new google.maps.Marker({
                            position: latlng,
                            map: map,
                        });

                        checkSubmitReady();
                    },
                    (error) => {
                        console.error(error);
                        gpsStatus.innerHTML = `<i class="bi bi-exclamation-triangle-fill text-danger"></i> Please enable location access in your browser.`;
                        alert('GPS Location is required for attendance. Please allow location access.');
                    },
                    { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                );
            } else {
                gpsStatus.innerHTML = "Geolocation is not supported by this browser.";
            }
        }

        async function startCamera() {
            try {
                stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' }, audio: false });
                video.srcObject = stream;
                video.style.display = 'block';
                photoPreview.style.display = 'none';
                
                document.getElementById('startCameraBtn').style.display = 'none';
                document.getElementById('captureBtn').style.display = 'inline-block';
                document.getElementById('retakeBtn').style.display = 'none';
                hasPhoto = false;
                checkSubmitReady();
            } catch (err) {
                console.error("Error accessing camera: ", err);
                alert("Could not access the camera. Please ensure you have granted camera permissions.");
            }
        }

        function capturePhoto() {
            if (!stream) return;
            
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
            
            let photoDataUrl = canvas.toDataURL('image/jpeg', 0.8);
            @this.set('photoData', photoDataUrl, false);
            
            photoPreview.src = photoDataUrl;
            video.style.display = 'none';
            photoPreview.style.display = 'block';
            
            // Stop camera stream
            stream.getTracks().forEach(track => track.stop());
            
            document.getElementById('captureBtn').style.display = 'none';
            document.getElementById('retakeBtn').style.display = 'inline-block';
            
            hasPhoto = true;
            checkSubmitReady();
        }

        function retakePhoto() {
            @this.set('photoData', null, false);
            startCamera();
        }

        function checkSubmitReady() {
            let submitBtn = document.getElementById('submitBtn');
            if (submitBtn) {
                let isBlockedByGeofence = submitBtn.hasAttribute('data-geofence-blocked');
                if (hasPhoto && hasLocation && !isBlockedByGeofence) {
                    submitBtn.removeAttribute('disabled');
                } else {
                    submitBtn.setAttribute('disabled', 'disabled');
                }
            }
        }
    </script>

    @endpush
</div>
