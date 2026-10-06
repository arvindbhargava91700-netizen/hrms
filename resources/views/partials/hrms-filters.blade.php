@php
    $filterEmployeesList = $this->getFilterEmployees($viewAnyPermission ?? null);
@endphp
<div class="card shadow-sm border-0 mb-4 bg-white rounded-3">
    <div class="card-body p-3">
        <form wire:submit.prevent="applyFilters" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1"><i class="bi bi-building me-1"></i>Branch</label>
                <select wire:model="filterBranchId" class="form-select form-select-sm bg-light border-0">
                    <option value="">All Branches</option>
                    @foreach($this->getFilterBranches() as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1"><i class="bi bi-diagram-3 me-1"></i>Department</label>
                <select wire:model="filterDepartmentId" class="form-select form-select-sm bg-light border-0">
                    <option value="">All Departments</option>
                    @foreach($this->getFilterDepartments() as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted mb-1"><i class="bi bi-search me-1"></i>Search Employee</label>
                <input type="text" wire:model="filterEmployeeSearch" class="form-control form-control-sm bg-light border-0" placeholder="Search by name, ID, or email...">
            </div>
            
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary w-100 fw-semibold">
                    <i class="bi bi-funnel"></i> Apply
                </button>
                @if($filterBranchId || $filterDepartmentId || $filterEmployeeSearch)
                <button type="button" wire:click="clearFilters" class="btn btn-sm btn-outline-secondary w-100 fw-semibold">
                    <i class="bi bi-x-circle"></i> Clear
                </button>
                @endif
            </div>
        </form>
    </div>
</div>
