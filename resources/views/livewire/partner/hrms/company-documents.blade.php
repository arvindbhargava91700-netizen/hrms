<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Company Documents</h4>
            <p class="text-muted mb-0 small">Manage and view company documents</p>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <div class="input-group input-group-sm" style="width: 250px;">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input wire:model.live.debounce.300ms="search" type="text" class="form-control border-start-0 ps-0" placeholder="Search documents...">
            </div>
            @if(auth()->user()->role !== 'employee')
                <button wire:click="createDocument" class="btn btn-primary btn-sm text-nowrap">
                    <i class="bi bi-plus-lg"></i> Add Document
                </button>
            @endif
        </div>
    </div>

    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="p-2">
        @if($documents->count() > 0)
            <div class="row">
                @foreach($documents as $doc)
                    <div class="col-md-4 col-lg-3 mb-4">
                        <div class="card h-100 shadow-sm border-0 position-relative" style="transition: transform 0.2s;">
                            
                            <!-- Action buttons top right -->
                            @if(auth()->user()->role !== 'employee')
                            <div class="position-absolute top-0 end-0 p-2 z-index-1" style="z-index: 10;">
                                <button wire:click="editDocument({{ $doc->id }})" class="btn btn-sm btn-light shadow-sm me-1 rounded-circle p-1" style="width: 32px; height: 32px;" title="Edit">
                                    <i class="bi bi-pencil text-primary" style="font-size: 0.9rem;"></i>
                                </button>
                                <button wire:click="deleteDocument({{ $doc->id }})" class="btn btn-sm btn-light shadow-sm rounded-circle p-1" style="width: 32px; height: 32px;" title="Delete" onclick="confirm('Are you sure you want to delete this document and its files?') || event.stopImmediatePropagation()">
                                    <i class="bi bi-trash text-danger" style="font-size: 0.9rem;"></i>
                                </button>
                            </div>
                            @endif

                            <!-- Files area (body) -->
                            <div class="card-body text-center d-flex flex-column align-items-center justify-content-center bg-light" style="min-height: 180px; border-radius: 0.375rem 0.375rem 0 0;">
                                @if($doc->files && count($doc->files) > 0)
                                    <div class="d-flex flex-wrap justify-content-center gap-2">
                                    @foreach($doc->files as $file)
                                        @php
                                            $ext = pathinfo($file, PATHINFO_EXTENSION);
                                            $isImage = in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                            $fileUrl = asset('storage/' . $file);
                                            
                                            $icon = 'bi-file-earmark-text';
                                            $color = 'text-secondary';
                                            if ($isImage) {
                                                $icon = 'bi-file-image';
                                                $color = 'text-primary';
                                            } elseif (strtolower($ext) === 'pdf') {
                                                $icon = 'bi-file-pdf';
                                                $color = 'text-danger';
                                            }
                                        @endphp
                                        <div class="p-2 border rounded bg-white shadow-sm" 
                                             style="cursor: pointer; transition: transform 0.2s;" 
                                             onclick="previewFile('{{ $fileUrl }}', '{{ $isImage ? 'image' : 'pdf' }}')"
                                             onmouseover="this.style.transform='scale(1.1)'"
                                             onmouseout="this.style.transform='scale(1)'">
                                            <i class="bi {{ $icon }} {{ $color }}" style="font-size: 2.5rem;"></i>
                                        </div>
                                    @endforeach
                                    </div>
                                @else
                                    <i class="bi bi-folder2-open text-muted" style="font-size: 3rem;"></i>
                                @endif
                            </div>

                            <!-- Footer area -->
                            <div class="card-footer bg-white border-top-0 p-3 pt-4">
                                <div class="d-flex justify-content-between align-items-end">
                                    <div style="max-width: 70%;">
                                        <h6 class="mb-0 fw-bold text-dark text-truncate" title="{{ $doc->title }}">{{ $doc->title }}</h6>
                                        <small class="text-muted"><i class="bi bi-calendar3 me-1"></i>{{ $doc->created_at->format('M d, Y') }}</small>
                                    </div>
                                    <div>
                                        @if($doc->status === 'active')
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1 rounded-pill">Active</span>
                                        @else
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2 py-1 rounded-pill">Inactive</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-4">
                {{ $documents->links() }}
            </div>
        @else
            <div class="text-center py-5 bg-white rounded shadow-sm border-0">
                <div class="mb-3">
                    <i class="bi bi-folder2-open text-muted" style="font-size: 3rem;"></i>
                </div>
                <h5 class="text-muted mb-1">No Company Documents Found</h5>
                <p class="text-muted small">Documents added will appear here.</p>
            </div>
        @endif
    </div>

    <!-- Create/Edit Modal -->
    <div class="modal fade {{ $isModalOpen ? 'show d-block' : '' }}" tabindex="-1" role="dialog" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold">{{ $editingId ? 'Edit Document' : 'Add New Document' }}</h5>
                    <button type="button" class="btn-close" wire:click="closeModal" aria-label="Close"></button>
                </div>
                <form wire:submit.prevent="saveDocument">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                            <input type="text" wire:model="title" class="form-control" placeholder="Enter document title">
                            @error('title') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Files (PDF or Images) <span class="text-danger">*</span></label>
                            <input type="file" wire:model="files" class="form-control" multiple accept=".pdf,.png,.jpg,.jpeg">
                            <div class="form-text text-muted">You can select multiple files at once. Max 10MB per file.</div>
                            <div wire:loading wire:target="files" class="text-primary small mt-1">Uploading...</div>
                            @error('files') <span class="text-danger small">{{ $message }}</span> @enderror
                            @error('files.*') <span class="text-danger small">{{ $message }}</span> @enderror
                            
                            @if($editingId)
                                <div class="form-text text-warning mt-2">
                                    <i class="bi bi-info-circle"></i> Note: Uploading new files will ADD to existing ones. If you want to replace them, you'd need to delete the old document and create a new one (or we can implement file deletion).
                                </div>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                            <select wire:model="status" class="form-select">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            @error('status') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" wire:click="closeModal" class="btn btn-secondary px-4">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="saveDocument">Save Document</span>
                            <span wire:loading wire:target="saveDocument">Saving...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- File Preview Modal -->
    <div class="modal fade" id="filePreviewModal" tabindex="-1" aria-hidden="true" wire:ignore>
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Document Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0 text-center bg-light" style="min-height: 500px; display: flex; align-items: center; justify-content: center;">
                    <iframe id="previewIframe" src="" style="width: 100%; height: 80vh; border: none; display: none;"></iframe>
                    <img id="previewImage" src="" style="max-width: 100%; max-height: 80vh; object-fit: contain; display: none;" />
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function previewFile(url, type) {
    const iframe = document.getElementById('previewIframe');
    const img = document.getElementById('previewImage');
    
    if (type === 'image') {
        iframe.style.display = 'none';
        iframe.src = '';
        img.src = url;
        img.style.display = 'block';
    } else {
        img.style.display = 'none';
        img.src = '';
        iframe.src = url;
        iframe.style.display = 'block';
    }
    
    var myModal = new bootstrap.Modal(document.getElementById('filePreviewModal'));
    myModal.show();
}

// Clean up modal backdrop if component refreshes
document.addEventListener('livewire:navigating', () => {
    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
});
</script>
