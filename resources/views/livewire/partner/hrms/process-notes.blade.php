<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Process Notes</h4>
            <p class="text-muted mb-0 small">Manage and view process notes</p>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <div class="input-group input-group-sm" style="width: 250px;">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input wire:model.live.debounce.300ms="search" type="text" class="form-control border-start-0 ps-0" placeholder="Search notes...">
            </div>
            @if(auth()->user()->role !== 'employee')
                <button wire:click="createNote" class="btn btn-primary btn-sm text-nowrap">
                    <i class="bi bi-plus-lg"></i> Add Note
                </button>
            @endif
        </div>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            @if($notes->count() > 0)
                <div class="accordion" id="processNotesAccordion">
                    @foreach($notes as $index => $note)
                        <div class="accordion-item mb-3 border rounded shadow-sm">
                            <h2 class="accordion-header" id="heading{{ $note->id }}">
                                <button class="accordion-button {{ $index !== 0 ? 'collapsed' : '' }} rounded fw-bold fs-5 text-dark bg-white" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $note->id }}" aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" aria-controls="collapse{{ $note->id }}">
                                    <div class="d-flex w-100 justify-content-between align-items-center me-3">
                                        <div>{{ $note->title }}</div>
                                        <div class="d-flex align-items-center gap-3">
                                            @if($note->status === 'active')
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1">Active</span>
                                            @else
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2 py-1">Inactive</span>
                                            @endif
                                            <span class="text-muted small d-none d-md-inline fw-normal"><i class="bi bi-calendar3 me-1"></i>{{ $note->created_at->format('M d, Y h:i A') }}</span>
                                        </div>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapse{{ $note->id }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" aria-labelledby="heading{{ $note->id }}" data-bs-parent="#processNotesAccordion">
                                <div class="accordion-body bg-light border-top">
                                    <div class="note-content text-dark mb-4 fs-6" style="line-height: 1.6;">
                                        {!! $note->description !!}
                                    </div>
                                    @if(auth()->user()->role !== 'employee')
                                        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                                            <button wire:click="editNote({{ $note->id }})" class="btn btn-sm btn-outline-primary px-3 shadow-sm d-flex align-items-center" title="Edit">
                                                <i class="bi bi-pencil me-2"></i> Edit
                                            </button>
                                            <button wire:click="deleteNote({{ $note->id }})" class="btn btn-sm btn-outline-danger px-3 shadow-sm d-flex align-items-center" title="Delete" onclick="confirm('Are you sure you want to delete this process note?') || event.stopImmediatePropagation()">
                                                <i class="bi bi-trash me-2"></i> Delete
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-5 text-muted bg-light rounded border border-dashed">
                    <i class="bi bi-journal-text text-secondary mb-3" style="font-size: 3.5rem;"></i>
                    <h5 class="fw-medium text-dark">No process notes found</h5>
                    <p class="mb-0">There are currently no process notes available to display.</p>
                </div>
            @endif
            
            @if($notes->hasPages())
                <div class="mt-4 d-flex justify-content-center">
                    {{ $notes->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modal for Create/Edit -->
    @if($isModalOpen)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom px-4">
                    <h5 class="modal-title fw-bold">{{ $editingId ? 'Edit' : 'Add' }} Process Note</h5>
                    <button type="button" class="btn-close" wire:click="$set('isModalOpen', false)"></button>
                </div>
                <div class="modal-body px-4 py-4">
                    <div class="mb-3">
                        <label class="form-label fw-medium">Title <span class="text-danger">*</span></label>
                        <input type="text" wire:model="title" class="form-control @error('title') is-invalid @enderror" placeholder="Enter process note title">
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium">Status <span class="text-danger">*</span></label>
                        <select wire:model="status" class="form-select @error('status') is-invalid @enderror">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3" wire:ignore>
                        <label class="form-label fw-medium">Description <span class="text-danger">*</span></label>
                        <div x-data="{ 
                                content: @entangle('description').live,
                                initQuill() {
                                    if (typeof Quill === 'undefined') return;
                                    let quill = new Quill($refs.editor, {
                                        theme: 'snow',
                                        modules: {
                                            toolbar: [
                                                ['bold', 'italic', 'underline', 'strike'],
                                                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                                                [{ 'header': [1, 2, 3, false] }],
                                                ['clean']
                                            ]
                                        },
                                        placeholder: 'Enter detailed process note...'
                                    });
                                    
                                    quill.root.innerHTML = this.content || '';
                                    
                                    quill.on('text-change', () => {
                                        this.content = quill.root.innerHTML;
                                    });
                                    
                                    this.$watch('content', (val) => {
                                        if (val !== quill.root.innerHTML) {
                                            quill.root.innerHTML = val || '';
                                        }
                                    });
                                }
                             }"
                             x-init="initQuill()">
                            <div x-ref="editor" class="bg-white" style="min-height: 150px; border-radius: 0 0 6px 6px;"></div>
                        </div>
                        @error('description') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="modal-footer border-top bg-light px-4">
                    <button type="button" class="btn btn-secondary" wire:click="$set('isModalOpen', false)">Cancel</button>
                    <button type="button" class="btn btn-primary px-4" wire:click="saveNote">
                        <i class="bi bi-check-circle me-1"></i> Save Process Note
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
</div>
