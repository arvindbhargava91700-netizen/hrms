<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0 bg-white">
                <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h4 class="mb-1 text-dark fw-bold">Attendance Calendar</h4>
                        <p class="text-muted mb-0">View monthly check-in history.</p>
                    </div>
                    
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <select wire:model.live="selectedEmployeeId" class="form-select shadow-sm border-0 bg-light" style="width: 250px;">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <option value="<?php echo e($employee->id); ?>"><?php echo e($employee->name); ?></option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </select>
                        
                        <div class="btn-group shadow-sm">
                            <button wire:click="previousMonth" class="btn btn-light border"><i class="bi bi-chevron-left"></i></button>
                            <button class="btn btn-white border fw-bold px-4" disabled><?php echo e($monthName); ?> <?php echo e($currentYear); ?></button>
                            <button wire:click="nextMonth" class="btn btn-light border"><i class="bi bi-chevron-right"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card shadow-sm border-0 bg-white overflow-hidden">
        <div class="card-body p-0">
            <div class="calendar-wrapper" style="overflow-x: auto;">
                <div style="min-width: 800px;" class="border-start border-top">
                    <div style="display: grid; grid-template-columns: repeat(7, 1fr);">
                        <!-- Days of week -->
                        <div class="border-bottom border-end text-center fw-bold text-muted bg-light py-3">Sunday</div>
                        <div class="border-bottom border-end text-center fw-bold text-muted bg-light py-3">Monday</div>
                        <div class="border-bottom border-end text-center fw-bold text-muted bg-light py-3">Tuesday</div>
                        <div class="border-bottom border-end text-center fw-bold text-muted bg-light py-3">Wednesday</div>
                        <div class="border-bottom border-end text-center fw-bold text-muted bg-light py-3">Thursday</div>
                        <div class="border-bottom border-end text-center fw-bold text-muted bg-light py-3">Friday</div>
                        <div class="border-bottom border-end text-center fw-bold text-muted bg-light py-3">Saturday</div>
                        
                        <!-- Empty padding days -->
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php for($i = 0; $i < $startDayOfWeek; $i++): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <div class="border-end border-bottom bg-light bg-opacity-50" style="min-height: 140px;"></div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        
                        <!-- Actual days -->
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php for($day = 1; $day <= $daysInMonth; $day++): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <?php 
                                $dateObj = \Carbon\Carbon::create($currentYear, $currentMonth, $day);
                                $isToday = $dateObj->isToday();
                                $attendance = $attendances[$day] ?? null;
                                $status = $dailyStatuses[$day] ?? '';
                                
                                $statusClass = '';
                                $statusIcon = '';
                                $statusBadge = '';
                                
                                switch($status) {
                                    case 'Punch Out':
                                        $statusClass = 'calendar-day-present';
                                        $statusIcon = '<i class="bi bi-check-circle-fill text-success" title="Punch Out"></i>';
                                        $statusBadge = '<span class="badge bg-success">Punch Out</span>';
                                        break;
                                    case 'Absent':
                                        $statusClass = 'border-start border-4 border-danger bg-danger bg-opacity-10';
                                        $statusIcon = '<i class="bi bi-x-circle-fill text-danger" title="Absent"></i>';
                                        $statusBadge = '<span class="badge bg-danger">Absent</span>';
                                        break;
                                    case 'Half Day':
                                        $statusClass = 'border-start border-4 border-warning bg-warning bg-opacity-10';
                                        $statusIcon = '<i class="bi bi-star-half text-warning" title="Half Day"></i>';
                                        $statusBadge = '<span class="badge bg-warning text-dark">Half Day</span>';
                                        break;
                                    case 'Holiday':
                                        $statusClass = 'border-start border-4 border-info bg-info bg-opacity-10';
                                        $statusIcon = '<i class="bi bi-calendar2-heart text-info" title="Holiday"></i>';
                                        $statusBadge = '<span class="badge bg-info text-dark">Holiday</span>';
                                        break;
                                    case 'Leave':
                                        $statusClass = 'border-start border-4 border-primary bg-primary bg-opacity-10';
                                        $statusIcon = '<i class="bi bi-airplane text-primary" title="Leave"></i>';
                                        $statusBadge = '<span class="badge bg-primary">Leave</span>';
                                        break;
                                    case 'Weekend':
                                    case 'Week Off':
                                        $statusClass = 'bg-light';
                                        $statusBadge = '<span class="badge bg-secondary">Week Off</span>';
                                        break;
                                    case 'Not Punch In':
                                        $statusClass = 'border-start border-4 border-secondary bg-secondary bg-opacity-10';
                                        $statusIcon = '<i class="bi bi-clock-history text-secondary" title="Not Punch In"></i>';
                                        $statusBadge = '<span class="badge bg-secondary">Not Punch In</span>';
                                        break;
                                }
                                
                                $clickable = in_array($status, ['Punch Out', 'Half Day']);
                            ?>
                            
                            <div class="border-end border-bottom p-2 position-relative <?php echo e($isToday ? 'bg-primary bg-opacity-10' : ''); ?> <?php echo e($statusClass); ?> <?php echo e($clickable ? 'cursor-pointer' : ''); ?>" 
                                 style="min-height: 140px; <?php echo e($clickable ? 'cursor: pointer;' : ''); ?>" 
                                 <?php if($clickable): ?> wire:click="viewDetails(<?php echo e($day); ?>)" <?php endif; ?>>
                                 
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge <?php echo e($isToday ? 'bg-primary shadow-sm' : 'bg-secondary bg-opacity-25 text-dark'); ?> fs-6 rounded-pill px-3"><?php echo e($day); ?></span>
                                    <span class="fs-5"><?php echo $statusIcon; ?></span>
                                </div>
                                
                                <div class="text-center mb-1">
                                    <?php echo $statusBadge; ?>

                                </div>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($attendance): ?>
                                    <div class="mt-2 text-center">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($attendance->check_in): ?>
                                            <div class="small fw-bold text-success mb-1 d-flex justify-content-center align-items-center">
                                                <i class="bi bi-box-arrow-in-right me-1"></i> <?php echo e(\Carbon\Carbon::parse($attendance->check_in)->format('h:i A')); ?>

                                            </div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($attendance->check_out): ?>
                                            <div class="small fw-bold text-danger d-flex justify-content-center align-items-center">
                                                <i class="bi bi-box-arrow-right me-1"></i> <?php echo e(\Carbon\Carbon::parse($attendance->check_out)->format('h:i A')); ?>

                                            </div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($clickable): ?>
                                    <div class="position-absolute bottom-0 end-0 p-1">
                                        <i class="bi bi-zoom-in text-muted small" style="opacity: 0.5;"></i>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        
                        <!-- Padding at end -->
                        <?php $remainingDays = 7 - (($daysInMonth + $startDayOfWeek) % 7); ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($remainingDays < 7): ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php for($i = 0; $i < $remainingDays; $i++): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <div class="border-end border-bottom bg-light bg-opacity-50" style="min-height: 140px;"></div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- View Details Modal -->
    <div class="modal fade" id="detailsModal" tabindex="-1" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light border-bottom-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-info-circle me-2 text-primary"></i>Attendance Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedAttendance): ?>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <h6 class="fw-bold border-bottom pb-2 text-success"><i class="bi bi-box-arrow-in-right me-2"></i>Check In</h6>
                                <p class="mb-2"><strong>Time:</strong> <?php echo e(\Carbon\Carbon::parse($selectedAttendance->check_in)->format('h:i A')); ?></p>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedAttendance->check_in_selfie): ?>
                                    <div class="mb-3 text-center">
                                        <img src="<?php echo e($selectedAttendance->check_in_selfie); ?>" class="img-fluid rounded shadow-sm border" style="max-height: 200px; object-fit: cover;">
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedAttendance->check_in_lat && $selectedAttendance->check_in_lng): ?>
                                    <p class="mb-1 small text-muted"><i class="bi bi-geo-alt"></i> <?php echo e($selectedAttendance->check_in_lat); ?>, <?php echo e($selectedAttendance->check_in_lng); ?></p>
                                    <div class="map-container rounded border bg-light" style="height: 150px; width: 100%;" id="mapCheckIn"></div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            
                            <div class="col-md-6">
                                <h6 class="fw-bold border-bottom pb-2 text-danger"><i class="bi bi-box-arrow-right me-2"></i>Check Out</h6>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedAttendance->check_out): ?>
                                    <p class="mb-2"><strong>Time:</strong> <?php echo e(\Carbon\Carbon::parse($selectedAttendance->check_out)->format('h:i A')); ?></p>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedAttendance->check_out_photo || $selectedAttendance->check_out_selfie): ?>
                                        <div class="mb-3 text-center">
                                            <img src="<?php echo e($selectedAttendance->check_out_selfie ?? $selectedAttendance->check_out_photo); ?>" class="img-fluid rounded shadow-sm border" style="max-height: 200px; object-fit: cover;">
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedAttendance->check_out_lat && $selectedAttendance->check_out_lng): ?>
                                        <p class="mb-1 small text-muted"><i class="bi bi-geo-alt"></i> <?php echo e($selectedAttendance->check_out_lat); ?>, <?php echo e($selectedAttendance->check_out_lng); ?></p>
                                        <div class="map-container rounded border bg-light" style="height: 150px; width: 100%;" id="mapCheckOut"></div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php else: ?>
                                    <div class="alert alert-warning py-2 small mt-2">
                                        <i class="bi bi-exclamation-triangle"></i> Not checked out yet.
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <div class="spinner-border text-primary mb-2" role="status"></div>
                            <p>Loading details...</p>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div class="modal-footer bg-light border-top-0">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo e(env('GOOGLE_MAPS_API_KEY')); ?>&libraries=places"></script>
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('open-details-modal', () => {
                var modal = new bootstrap.Modal(document.getElementById('detailsModal'));
                modal.show();
                
                // Allow modal to render then init maps
                setTimeout(() => {
                    initMaps();
                }, 500);
            });
        });

        function initMaps() {
            // we will need to read data from the DOM or wire variable. 
            // Better to read from data attributes we could inject, but since we are Livewire driven, 
            // the DOM will have the lat lng printed in text. We can fetch it via Livewire component.
            window.Livewire.find('<?php echo e($_instance->getId()); ?>').get('selectedAttendance').then(att => {
                if(att) {
                    if (att.check_in_lat && att.check_in_lng) {
                        let locIn = {lat: parseFloat(att.check_in_lat), lng: parseFloat(att.check_in_lng)};
                        let mapIn = new google.maps.Map(document.getElementById('mapCheckIn'), {
                            zoom: 15,
                            center: locIn,
                            disableDefaultUI: true,
                        });
                        new google.maps.Marker({ position: locIn, map: mapIn });
                    }
                    
                    if (att.check_out_lat && att.check_out_lng) {
                        let locOut = {lat: parseFloat(att.check_out_lat), lng: parseFloat(att.check_out_lng)};
                        let mapOut = new google.maps.Map(document.getElementById('mapCheckOut'), {
                            zoom: 15,
                            center: locOut,
                            disableDefaultUI: true,
                        });
                        new google.maps.Marker({ position: locOut, map: mapOut });
                    }
                }
            });
        }
    </script>
    <?php $__env->stopPush(); ?>
</div><?php /**PATH C:\xampp\htdocs\life_infotech\hrms\resources\views/livewire/partner/hrms/attendance/attendancecalendar.blade.php ENDPATH**/ ?>