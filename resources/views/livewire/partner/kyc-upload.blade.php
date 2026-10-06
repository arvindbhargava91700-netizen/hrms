<div>

    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">KYC & Bank Details</h5>
                    @if($kyc)
                        <span class="badge-status badge-{{ $kyc->status }}">Status: {{ $kyc->status }}</span>
                    @endif
                </div>
                <div class="card-body">
                    @if($kyc && $kyc->status === 'approved')
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle-fill me-2"></i> Your KYC has been approved. You can now use all features.
                        </div>
                    @endif

                    @if($kyc && $kyc->status === 'rejected')
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i> Your previous submission was rejected. Please review and resubmit.
                        </div>
                    @endif

                    <form wire:submit="submit">
                        @foreach($requirements as $requirement)
                            <div class="mb-4">
                                <h6 class="fw-700 mb-2 text-primary">{{ $requirement['label'] }}</h6>
                                @if(!empty($requirement['help_text']))
                                    <div class="text-muted small mb-3">{{ $requirement['help_text'] }}</div>
                                @endif

                                @if($requirement['field_type'] === 'text')
                                    <label class="form-label">{{ $requirement['label'] }} @if($requirement['is_required'])<span class="text-danger">*</span>@endif</label>
                                    <input
                                        type="{{ $requirement['input_type'] === 'number' ? 'number' : 'text' }}"
                                        class="form-control"
                                        wire:model="textValues.{{ $requirement['key'] }}"
                                        placeholder="{{ $requirement['placeholder'] ?? '' }}"
                                        @disabled($kyc?->status === 'approved')
                                    >
                                    @error('textValues.' . $requirement['key']) <span class="text-danger small">{{ $message }}</span> @enderror
                                @else
                                    @if(($requirement['has_value_field'] ?? true) === true)
                                        <label class="form-label">{{ $requirement['value_label'] ?? ($requirement['label'] . ' Number') }} @if($requirement['is_required'])<span class="text-danger">*</span>@endif</label>
                                        <input
                                            type="text"
                                            class="form-control mb-3"
                                            wire:model="documentNumbers.{{ $requirement['key'] }}"
                                            placeholder="{{ $requirement['placeholder'] ?? '' }}"
                                            @disabled($kyc?->status === 'approved')
                                        >
                                        @error('documentNumbers.' . $requirement['key']) <span class="text-danger small d-block mb-2">{{ $message }}</span> @enderror
                                    @endif

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Front @if($requirement['is_required'])<span class="text-danger">*</span>@endif</label>
                                            <input
                                                type="file"
                                                class="form-control"
                                                wire:model="documentFiles.{{ $requirement['key'] }}.front"
                                                @disabled($kyc?->status === 'approved')
                                            >
                                            @error('documentFiles.' . $requirement['key'] . '.front') <span class="text-danger small">{{ $message }}</span> @enderror
                                        </div>

                                        @if(($requirement['document_mode'] ?? 'single') === 'front_back')
                                            <div class="col-md-6">
                                                <label class="form-label">Back @if($requirement['is_required'])<span class="text-danger">*</span>@endif</label>
                                                <input
                                                    type="file"
                                                    class="form-control"
                                                    wire:model="documentFiles.{{ $requirement['key'] }}.back"
                                                    @disabled($kyc?->status === 'approved')
                                                >
                                                @error('documentFiles.' . $requirement['key'] . '.back') <span class="text-danger small">{{ $message }}</span> @enderror
                                            </div>
                                        @endif
                                    </div>

                                    @if(!empty($documentFiles[$requirement['key']]))
                                        <div class="mt-3 small text-muted">
                                            <strong>Uploaded files:</strong>
                                            @foreach($documentFiles[$requirement['key']] as $side => $file)
                                                @php
                                                    $fileLabel = is_string($file) ? basename($file) : 'Selected file';
                                                @endphp
                                                <div>{{ ucfirst($side) }}: {{ $fileLabel }}</div>
                                            @endforeach
                                        </div>
                                    @endif
                                @endif
                            </div>
                        @endforeach

                        @if($kyc?->status !== 'approved')
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary px-4 fw-600">Submit Details</button>
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
