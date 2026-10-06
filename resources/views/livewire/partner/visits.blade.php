<div>

    <div class="card">

        <div class="card-header d-flex justify-content-between align-items-center">

            <h6 class="mb-0">Visit Requests</h6>

        </div>

        <div class="card-body">

            @if(session('success'))

            <div class="alert alert-success">{{ session('success') }}</div>

            @endif



            @if($visits->count())

            <table class="table table-striped">

                <thead>

                    <tr>

                        <th>#</th>

                        <th>Customer</th>

                        <th>Listing</th>

                        <th>When</th>

                        <th>Note</th>

                        <th>Status</th>

                        <th>Actions</th>

                    </tr>

                </thead>

                <tbody>

                    @foreach($visits as $v)

                    <tr>

                        <td><span title="{{ $v->id }}">{{ substr($v->id, 0, 8) }}</span></td>

                        <td>{{ $v->customer->name }}<br><small>{{ $v->customer->mobile }}</small></td>

                        <td>{{ $v->listing->title }}</td>

                        <td>{{ $v->visit_date }} {{ $v->visit_time }}</td>

                        <td>{{ $v->note }}</td>

                        <td>{{ ucfirst($v->status) }}</td>

                        <td>

                            @if($v->status === 'pending')

                            <button class="btn btn-sm btn-success" onclick="let note = prompt('Enter an acceptance note (optional):'); if(note !== null) @this.call('approve', '{{ $v->id }}', note)">Accept</button>

                            <button class="btn btn-sm btn-danger" onclick="let note = prompt('Enter a rejection reason (optional):'); if(note !== null) @this.call('reject', '{{ $v->id }}', note)">Reject</button>

                            @else

                            <span class="text-muted">No actions</span>

                            @endif

                        </td>

                    </tr>

                    @endforeach

                </tbody>

            </table>



            <div class="mt-3">{{ $visits->links() }}</div>

            @else

            <div class="alert alert-info">No visit requests yet.</div>

            @endif

        </div>

    </div>

</div>

