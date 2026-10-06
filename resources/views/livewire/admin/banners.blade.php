<div>


    <div class="d-flex justify-content-between align-items-center mb-4">


        <div>


            <h5 class="mb-0">App Banners</h5>


            <small class="text-muted">Manage promotional banners for the mobile app home screen.</small>


        </div>


        <button class="btn btn-primary" wire:click="create">


            <i class="bi bi-plus-lg me-2"></i>Add Banner


        </button>


    </div>





    <div class="row g-4">


        @forelse ($banners as $banner)


            <div class="col-md-6 col-lg-4">


                <div class="card h-100 shadow-sm border-0">


                    <img src="{{ asset('storage/' . $banner->image_path) }}" class="card-img-top" style="height: 200px; object-fit: cover;" alt="{{ $banner->title }}">


                    <div class="card-body d-flex flex-column">


                        <div class="d-flex justify-content-between align-items-start mb-2">


                            <div>


                                <h6 class="card-title mb-1 fw-bold">{{ $banner->title }}</h6>


                                <p class="card-text small text-muted">{{ $banner->category_id ? $banner->category->name : 'Global Banner' }}</p>


                            </div>


                            <span class="badge {{ $banner->is_active ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">


                                {{ $banner->is_active ? 'Active' : 'Inactive' }}


                            </span>


                        </div>


                        


                        <div class="mt-auto d-flex justify-content-between align-items-center pt-3 border-top border-light">


                            <span class="small text-muted">Sort: {{ $banner->sort_order }}</span>


                            <div class="btn-group">


                                <button wire:click="toggleActive({{ $banner->id }})" class="btn btn-sm btn-outline-secondary" title="Toggle Status">


                                    <i class="bi bi-power"></i>


                                </button>


                                <button wire:click="edit({{ $banner->id }})" class="btn btn-sm btn-outline-primary" title="Edit">


                                    <i class="bi bi-pencil"></i>


                                </button>


                                <button wire:confirm="Are you sure you want to delete this banner?" wire:click="delete({{ $banner->id }})" class="btn btn-sm btn-outline-danger" title="Delete">


                                    <i class="bi bi-trash"></i>


                                </button>


                            </div>


                        </div>


                    </div>


                </div>


            </div>


        @empty


            <div class="col-12">


                <div class="card border-0 shadow-sm">


                    <div class="card-body text-center text-muted py-5">


                        <i class="bi bi-images fs-1 d-block mb-3"></i>


                        No banners found. Click "Add Banner" to upload one.


                    </div>


                </div>


            </div>


        @endforelse


    </div>





    {{-- Modal --}}


    @if($showModal)


        <div class="modal d-block" style="background:rgba(0,0,0,0.5);">


            <div class="modal-dialog modal-dialog-centered">


                <div class="modal-content">


                    <div class="modal-header">


                        <h5 class="modal-title">{{ $bannerId ? 'Edit Banner' : 'Upload Banner' }}</h5>


                        <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>


                    </div>


                    <form wire:submit="save">


                        <div class="modal-body">


                            <div class="mb-3">


                                <label class="form-label">Title / Alt Text</label>


                                <input type="text" wire:model="title" class="form-control">


                                @error('title') <span class="text-danger small">{{ $message }}</span> @enderror


                            </div>


                            


                            <div class="mb-3">


                                <label class="form-label">Banner Image <small class="text-muted">(Must be exactly 1200x500 pixels)</small></label>


                                @if($existing_image)


                                    <div class="mb-2">


                                        <img src="{{ asset('storage/' . $existing_image) }}" class="img-thumbnail" style="height: 100px; object-fit: cover;">


                                    </div>


                                @endif


                                <input type="file" wire:model="image" accept="image/*" class="form-control">


                                @error('image') <span class="text-danger small">{{ $message }}</span> @enderror


                            </div>





                            <div class="mb-3">


                                <label class="form-label">Category Scope</label>


                                <select wire:model="category_id" class="form-select">


                                    <option value="">Global Banner (Shows Everywhere)</option>


                                    @foreach($categories as $id => $catName)


                                        <option value="{{ $id }}">{{ $catName }} Only</option>


                                    @endforeach


                                </select>


                            </div>





                            <div class="row">


                                <div class="col-6">


                                    <label class="form-label">Sort Order</label>


                                    <input type="number" wire:model="sort_order" class="form-control">


                                </div>


                                <div class="col-6 d-flex align-items-end pb-1">


                                    <div class="form-check form-switch">


                                        <input class="form-check-input" type="checkbox" id="isBannerActive" wire:model="is_active">


                                        <label class="form-check-label" for="isBannerActive">Active</label>


                                    </div>


                                </div>


                            </div>


                        </div>


                        <div class="modal-footer">


                            <button type="button" class="btn btn-outline-secondary" wire:click="$set('showModal', false)">Cancel</button>


                            <button type="submit" class="btn btn-primary">Save Banner</button>


                        </div>


                    </form>


                </div>


            </div>


        </div>


    @endif


</div>


