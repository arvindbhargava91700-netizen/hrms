<div class="container-fluid py-3">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('success')): ?>
        <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show rounded-3 mb-4">
            <i class="bi bi-check-circle-fill me-2"></i><?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('error')): ?>
        <div class="alert alert-danger border-0 shadow-sm alert-dismissible fade show rounded-3 mb-4">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo e(session('error')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>


    <!-- Live Geofence & Location Verification Status Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 <?php echo e($working_mode === 'office' ? ($isWithinGeofence ? 'bg-success bg-opacity-10 border-success' : 'bg-danger bg-opacity-10 border-danger') : 'bg-primary bg-opacity-10 border-primary'); ?>" style="border-left: 6px solid <?php echo e($working_mode === 'office' ? ($isWithinGeofence ? '#198754' : '#dc3545') : '#0d6efd'); ?> !important;">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3.5">
                    <div class="rounded-circle p-3 d-flex align-items-center justify-content-center text-white shadow-sm flex-shrink-0" style="width: 52px; height: 52px; background: <?php echo e($working_mode === 'office' ? ($isWithinGeofence ? '#198754' : '#dc3545') : '#0d6efd'); ?>;">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($working_mode === 'office'): ?>
                            <i class="bi <?php echo e($isWithinGeofence ? 'bi-geo-alt-fill' : 'bi-exclamation-triangle-fill'); ?> fs-3"></i>
                        <?php elseif($working_mode === 'remote'): ?>
                            <i class="bi bi-house-door-fill fs-3"></i>
                        <?php else: ?>
                            <i class="bi bi-briefcase-fill fs-3"></i>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2 flex-wrap" style="font-size:1rem;">
                            <span>Working Mode: <strong><?php echo e(ucfirst($working_mode)); ?> Work</strong></span>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($working_mode === 'office'): ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isWithinGeofence): ?>
                                    <span class="badge bg-success rounded-pill px-3 py-1.5 fs-7"><i class="bi bi-check-circle-fill me-1"></i>Inside Office Geofence Range</span>
                                <?php else: ?>
                                    <span class="badge bg-danger rounded-pill px-3 py-1.5 fs-7"><i class="bi bi-x-circle-fill me-1"></i>Out of Geofence Range</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php else: ?>
                                <span class="badge bg-primary rounded-pill px-3 py-1.5 fs-7"><i class="bi bi-shield-check me-1"></i>Geofence Exempt</span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </h6>
                        <p class="mb-0 text-secondary small">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($working_mode === 'office'): ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($officeLat) && !empty($officeLng)): ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!is_null($distanceMeters)): ?>
                                        Office Allowed Radius: <strong><?php echo e($officeRadius); ?>m</strong> | Your Distance to Office: <strong class="<?php echo e($isWithinGeofence ? 'text-success' : 'text-danger'); ?> fs-6"><?php echo e($distanceMeters); ?> meters</strong>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$isWithinGeofence): ?>
                                            <span class="text-danger fw-bold d-block mt-1"><i class="bi bi-slash-circle me-1"></i> Attendance punch is blocked because you are outside the <?php echo e($officeRadius); ?>m office range limit!</span>
                                        <?php else: ?>
                                            <span class="text-success fw-bold d-block mt-1"><i class="bi bi-check-circle me-1"></i> Location verified! You are inside the office range. Attendance allowed.</span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php else: ?>
                                        Office Allowed Radius: <strong><?php echo e($officeRadius); ?>m</strong> | <span class="text-muted"><i class="bi bi-clock me-1"></i>Fetching live GPS distance...</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted"><i class="bi bi-info-circle me-1"></i>Office GPS coordinates not configured in HRMS Settings. Geofence check is temporarily bypassed.</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php elseif($working_mode === 'remote'): ?>
                                <i class="bi bi-info-circle me-1 text-primary"></i>Selfie + GPS Lat/Lon will be logged for Remote/WFH record. Range restriction is exempt.
                            <?php else: ?>
                                <i class="bi bi-info-circle me-1 text-primary"></i>Selfie + GPS Lat/Lon will be logged for Field Work location tracking. Range restriction is exempt.
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isHoliday): ?>
                        <div class="alert alert-info border-0 shadow-sm text-center py-5">
                            <i class="bi bi-calendar-event text-info display-4 mb-3 d-block"></i>
                            <h4 class="fw-bold">Today is a Holiday</h4>
                            <p class="text-muted mb-0">It's <strong><?php echo e($holidayName); ?></strong> today! Enjoy your day off.</p>
                        </div>
                    <?php elseif($isWeekOff): ?>
                        <div class="alert alert-secondary border-0 shadow-sm text-center py-5">
                            <i class="bi bi-cup-hot text-secondary display-4 mb-3 d-block"></i>
                            <h4 class="fw-bold">Today is a Week Off</h4>
                            <p class="text-muted mb-0">Enjoy your weekend!</p>
                        </div>
                    <?php elseif($isFullLeave): ?>
                        <div class="alert alert-warning border-0 shadow-sm text-center py-5">
                            <i class="bi bi-umbrella text-warning display-4 mb-3 d-block"></i>
                            <h4 class="fw-bold">You are on Leave</h4>
                            <p class="text-muted mb-0">You have an approved full-day leave for today. Have a great day!</p>
                        </div>
                    <?php elseif($todayAttendance && $todayAttendance->check_out): ?>
                        <div class="alert <?php echo e($todayAttendance->status === 'absent' ? 'alert-danger' : ($todayAttendance->status === 'half_day' ? 'alert-warning' : 'alert-success')); ?> border-0 shadow-sm text-center py-4">
                            <i class="bi <?php echo e($todayAttendance->status === 'absent' ? 'bi-exclamation-triangle' : 'bi-check-circle'); ?> display-4 mb-3 d-block"></i>
                            <h4 class="fw-bold">Attendance Completed</h4>
                            <p class="text-muted mb-2">You have already checked out for today.</p>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($todayAttendance->status === 'absent'): ?>
                                <div class="badge bg-danger rounded-pill px-3 py-2 fs-6">
                                    <i class="bi bi-x-circle me-1"></i>Status: Absent (Short Working Hours)
                                </div>
                                <div class="small text-danger mt-2">
                                    Worked: <?php echo e(floor(($todayAttendance->working_minutes ?? 0) / 60)); ?>h <?php echo e(($todayAttendance->working_minutes ?? 0) % 60); ?>m (Did not meet minimum half-day requirement)
                                </div>
                            <?php elseif($todayAttendance->status === 'half_day'): ?>
                                <div class="badge bg-warning text-dark rounded-pill px-3 py-2 fs-6">
                                    <i class="bi bi-clock-half me-1"></i>Status: Half Day
                                </div>
                                <div class="small text-muted mt-2">
                                    Worked: <?php echo e(floor(($todayAttendance->working_minutes ?? 0) / 60)); ?>h <?php echo e(($todayAttendance->working_minutes ?? 0) % 60); ?>m
                                </div>
                            <?php else: ?>
                                <div class="badge bg-success rounded-pill px-3 py-2 fs-6">
                                    <i class="bi bi-check2-all me-1"></i>Status: Present
                                </div>
                                <div class="small text-muted mt-2">
                                    Worked: <?php echo e(floor(($todayAttendance->working_minutes ?? 0) / 60)); ?>h <?php echo e(($todayAttendance->working_minutes ?? 0) % 60); ?>m
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php elseif($isAbsent): ?>
                        <div class="alert alert-danger border-0 shadow-sm text-center py-5">
                            <i class="bi bi-x-circle text-danger display-4 mb-3 d-block"></i>
                            <h4 class="fw-bold">You are Marked Absent</h4>
                            <p class="text-muted mb-0">You did not check in within the scheduled shift/cutoff time. Attendance marking is closed for today.</p>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isHalfLeave): ?>
                        <div class="alert alert-primary border-0 shadow-sm text-center py-3 mb-4">
                            <h6 class="fw-bold mb-1"><i class="bi bi-clock-half me-2"></i>Half-Day Leave</h6>
                            <p class="text-muted mb-0 small">You are on a half-day leave. Please log your partial hours.</p>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    
                    <div style="display: <?php echo e(($isHoliday || $isWeekOff || $isFullLeave || $isAbsent || ($todayAttendance && $todayAttendance->check_out)) ? 'none' : 'block'); ?>">
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

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if((!$todayAttendance || !$todayAttendance->check_out) && count($checklists) > 0): ?>
                        <div class="mt-4 pt-3 border-top">
                            <h6 class="fw-bold mb-3"><i class="bi bi-card-checklist me-2 text-primary"></i><?php echo e((!$todayAttendance || !$todayAttendance->check_in) ? 'Pre-Check-in Checklist' : 'Pre-Check-out Checklist'); ?></h6>
                            <div class="alert alert-light border shadow-sm">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $checklists; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $checklist): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 <?php echo e(!$loop->last ? 'border-bottom' : ''); ?>">
                                        <div class="fw-600 text-dark">
                                            <?php echo e($checklist->question); ?>

                                        </div>
                                        <div class="d-flex gap-2">
                                            <input type="radio" class="btn-check" wire:model="checklistAnswers.<?php echo e($checklist->id); ?>" name="chk_<?php echo e($checklist->id); ?>" id="chk_yes_<?php echo e($checklist->id); ?>" value="1" autocomplete="off">
                                            <label class="btn btn-outline-success btn-sm px-3" for="chk_yes_<?php echo e($checklist->id); ?>">Yes</label>

                                            <input type="radio" class="btn-check" wire:model="checklistAnswers.<?php echo e($checklist->id); ?>" name="chk_<?php echo e($checklist->id); ?>" id="chk_no_<?php echo e($checklist->id); ?>" value="0" autocomplete="off">
                                            <label class="btn btn-outline-danger btn-sm px-3" for="chk_no_<?php echo e($checklist->id); ?>">No</label>
                                        </div>
                                    </div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['checklistAnswers.'.$checklist->id];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-danger small mt-n2 mb-2"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <div class="d-grid gap-2 d-md-block text-center mt-4">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($todayAttendance && $todayAttendance->check_in && !$todayAttendance->check_out): ?>
                            <button type="button" id="submitBtn" wire:click="processCheckInOut" class="btn btn-danger btn-lg px-md-5 shadow-sm" disabled wire:loading.attr="disabled" <?php if($working_mode === 'office' && !$isWithinGeofence && !empty($officeLat) && !empty($officeLng)): ?> data-geofence-blocked="true" <?php endif; ?>>
                                <i class="bi bi-box-arrow-right me-2"></i> Check Out
                            </button>
                        <?php elseif(!$todayAttendance || !$todayAttendance->check_in): ?>
                            <button type="button" id="submitBtn" wire:click="processCheckInOut" class="btn btn-success btn-lg px-md-5 shadow-sm" disabled wire:loading.attr="disabled" <?php if($working_mode === 'office' && !$isWithinGeofence && !empty($officeLat) && !empty($officeLng)): ?> data-geofence-blocked="true" <?php endif; ?>>
                                <i class="bi bi-box-arrow-in-right me-2"></i> Check In
                            </button>
                        <?php else: ?>
                            <div class="alert alert-info border-0 shadow-sm">
                                <i class="bi bi-info-circle me-2"></i> You have already checked out for today.
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        
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
                    <div class="d-flex align-items-center mb-3 p-3 bg-light rounded-3">
                        <div class="display-4 me-3 text-primary"><i class="bi bi-calendar2-day"></i></div>
                        <div>
                            <h4 class="mb-0"><?php echo e(now()->format('l, F j, Y')); ?></h4>
                            <p class="text-muted mb-0">Current Date</p>
                        </div>
                    </div>

                    <?php
                        $userShift = auth()->user()->shift;
                    ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($userShift): ?>
                        <div class="d-flex align-items-center justify-content-between p-3 mb-3 bg-primary bg-opacity-10 border border-primary border-opacity-25 rounded-3">
                            <div>
                                <span class="badge bg-primary px-2 py-1 mb-1"><i class="bi bi-clock me-1"></i>Shift Timings</span>
                                <h6 class="mb-0 fw-bold text-primary"><?php echo e($userShift->name); ?></h6>
                            </div>
                            <div class="text-end">
                                <span class="fw-bold text-dark fs-6">
                                    <?php echo e(\Carbon\Carbon::parse($userShift->start_time)->format('h:i A')); ?> - <?php echo e(\Carbon\Carbon::parse($userShift->end_time)->format('h:i A')); ?>

                                </span>
                                <div class="small text-muted">Min Half-Day: <?php echo e($userShift->min_half_day_mins ? round($userShift->min_half_day_mins / 60, 1) . ' hrs' : '2 hrs'); ?></div>
                            </div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($todayAttendance): ?>
                    <div class="mb-4 p-3 border rounded-3 bg-white text-center shadow-sm">
                        <h6 class="text-uppercase fw-bold text-muted mb-2">Current Status</h6>
                        <div class="d-flex justify-content-center align-items-center gap-2 flex-wrap mb-1">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($todayAttendance->status === 'punch_out'): ?>
                                <span class="badge bg-success rounded-pill px-4 py-2 fs-6">Punch Out (Present)</span>
                            <?php elseif($todayAttendance->status === 'absent'): ?>
                                <span class="badge bg-danger rounded-pill px-4 py-2 fs-6">Absent</span>
                            <?php elseif($todayAttendance->status === 'half_day'): ?>
                                <span class="badge bg-warning text-dark rounded-pill px-4 py-2 fs-6">Half Day</span>
                            <?php elseif($todayAttendance->status === 'short_leave'): ?>
                                <span class="badge bg-info rounded-pill px-4 py-2 fs-6">Short Leave</span>
                            <?php elseif($todayAttendance->status === 'punch_in'): ?>
                                <span class="badge bg-primary rounded-pill px-4 py-2 fs-6">Punch In</span>
                            <?php else: ?>
                                <span class="badge bg-secondary rounded-pill px-4 py-2 fs-6"><?php echo e(ucfirst(str_replace('_', ' ', $todayAttendance->status))); ?></span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <span class="badge bg-dark bg-gradient rounded-pill px-3 py-2 fs-6">
                                <i class="bi bi-laptop me-1"></i><?php echo e(ucfirst($todayAttendance->working_mode ?: 'office')); ?> Mode
                            </span>
                        </div>
                        <div class="d-flex justify-content-center align-items-center gap-2 flex-wrap mt-2">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($todayAttendance->working_minutes) && $todayAttendance->working_minutes > 0): ?>
                                <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-2">
                                    <i class="bi bi-hourglass-split me-1"></i>Worked: <?php echo e(floor($todayAttendance->working_minutes / 60)); ?>h <?php echo e($todayAttendance->working_minutes % 60); ?>m
                                </span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($todayAttendance->late_minutes) && $todayAttendance->late_minutes > 0): ?>
                                <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3 py-2">
                                    <i class="bi bi-clock-history me-1"></i>Late by <?php echo e($todayAttendance->late_minutes); ?> mins
                                </span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="p-3 border rounded-3 position-relative overflow-hidden h-100 <?php echo e(($todayAttendance && $todayAttendance->check_in) ? 'bg-success bg-opacity-10 border-success' : 'bg-light'); ?>">
                                <p class="text-muted small text-uppercase fw-bold mb-1">Check In Time</p>
                                <h3 class="mb-0 <?php echo e(($todayAttendance && $todayAttendance->check_in) ? 'text-success' : 'text-dark'); ?>">
                                    <?php echo e(($todayAttendance && $todayAttendance->check_in) ? \Carbon\Carbon::parse($todayAttendance->check_in)->format('h:i A') : '--:-- --'); ?>

                                </h3>
                                <?php
                                    $inSelfie = $todayAttendance ? ($todayAttendance->check_in_selfie_url ?: $todayAttendance->check_in_selfie) : null;
                                ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($inSelfie): ?>
                                    <div class="mt-2 d-flex align-items-center gap-2">
                                        <a href="javascript:void(0);" onclick="showSelfieModal('<?php echo e($inSelfie); ?>', 'Check-In Selfie')" title="Click to enlarge">
                                            <img src="<?php echo e($inSelfie); ?>" alt="Check In Selfie" class="img-thumbnail shadow-sm" style="height: 65px; width: 65px; object-fit: cover; border-radius: 8px; cursor: pointer;" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=In&background=10b981&color=fff';">
                                        </a>
                                        <span class="badge bg-light text-muted border small"><i class="bi bi-camera me-1"></i>Check-in Selfie</span>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="p-3 border rounded-3 position-relative overflow-hidden h-100 <?php echo e(($todayAttendance && $todayAttendance->check_out) ? 'bg-danger bg-opacity-10 border-danger' : 'bg-light'); ?>">
                                <p class="text-muted small text-uppercase fw-bold mb-1">Check Out Time</p>
                                <h3 class="mb-0 <?php echo e(($todayAttendance && $todayAttendance->check_out) ? 'text-danger' : 'text-dark'); ?>">
                                    <?php echo e(($todayAttendance && $todayAttendance->check_out) ? \Carbon\Carbon::parse($todayAttendance->check_out)->format('h:i A') : '--:-- --'); ?>

                                </h3>
                                <?php
                                    $outSelfie = $todayAttendance ? ($todayAttendance->check_out_selfie_url ?: $todayAttendance->check_out_selfie) : null;
                                ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($outSelfie): ?>
                                    <div class="mt-2 d-flex align-items-center gap-2">
                                        <a href="javascript:void(0);" onclick="showSelfieModal('<?php echo e($outSelfie); ?>', 'Check-Out Selfie')" title="Click to enlarge">
                                            <img src="<?php echo e($outSelfie); ?>" alt="Check Out Selfie" class="img-thumbnail shadow-sm" style="height: 65px; width: 65px; object-fit: cover; border-radius: 8px; cursor: pointer;" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=Out&background=ef4444&color=fff';">
                                        </a>
                                        <span class="badge bg-light text-muted border small"><i class="bi bi-camera me-1"></i>Check-out Selfie</span>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Selfie Preview Modal -->
    <div class="modal fade" id="selfiePreviewModal" tabindex="-1" aria-labelledby="selfiePreviewModalLabel" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow rounded-4 overflow-hidden">
                <div class="modal-header bg-light py-3 px-4">
                    <h6 class="modal-title fw-bold text-dark" id="selfiePreviewModalLabel">
                        <i class="bi bi-image me-2 text-primary"></i>Selfie Preview
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0 text-center bg-black">
                    <img id="selfieModalImg" src="" alt="Selfie Preview" class="img-fluid" style="max-height: 80vh; object-fit: contain;">
                </div>
                <div class="modal-footer bg-light py-2 px-3 justify-content-between">
                    <a id="selfieModalDownload" href="" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Open Full Image
                    </a>
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
    
    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo e(env('GOOGLE_MAPS_API_KEY')); ?>&libraries=places"></script>
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
                        window.Livewire.find('<?php echo e($_instance->getId()); ?>').set('latitude', lat, false);
                        window.Livewire.find('<?php echo e($_instance->getId()); ?>').set('longitude', lng, false);
                        window.Livewire.find('<?php echo e($_instance->getId()); ?>').call('checkGeofenceStatus', lat, lng);
                        
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
            window.Livewire.find('<?php echo e($_instance->getId()); ?>').set('photoData', photoDataUrl, false);
            
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
            window.Livewire.find('<?php echo e($_instance->getId()); ?>').set('photoData', null, false);
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

        function showSelfieModal(imgUrl, title) {
            let modalImg = document.getElementById('selfieModalImg');
            let modalDownload = document.getElementById('selfieModalDownload');
            let modalLabel = document.getElementById('selfiePreviewModalLabel');
            if (modalImg) modalImg.src = imgUrl;
            if (modalDownload) modalDownload.href = imgUrl;
            if (modalLabel && title) modalLabel.innerHTML = '<i class="bi bi-camera me-2 text-primary"></i>' + title;
            let modalEl = document.getElementById('selfiePreviewModal');
            if (modalEl) {
                let modal = new bootstrap.Modal(modalEl);
                modal.show();
            }
        }
    </script>

    <?php $__env->stopPush(); ?>
</div>
<?php /**PATH C:\xampp\htdocs\life_infotech\hrms\resources\views/livewire/partner/hrms/attendance/mark-attendance.blade.php ENDPATH**/ ?>